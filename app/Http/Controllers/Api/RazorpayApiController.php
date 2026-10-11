<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Services\Order\OrderService;
use App\Services\Payment\RazorpayPaymentService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RazorpayApiController extends Controller
{
    public function __construct(
        protected RazorpayPaymentService $razorpayService,
        protected OrderService $orderService
    ) {}

    /**
     * Create Razorpay Order
     *
     * Endpoint: POST /api/create-order
     * Calls Razorpay API: POST https://api.razorpay.com/v1/orders
     * Request: { amount (paise), currency, receipt }
     * Return: { order_id, amount, currency }
     * Minimum amount: 100 paise
     */
    public function createOrder(Request $request): JsonResponse
    {
        // 1. Amount validation & resolution
        $rawAmount = $request->input('amount');

        if ($rawAmount === null || $rawAmount === '') {
            $product = Product::where('active', true)->first();
            $amount = $product ? (int) round($product->effective_price * 100) : 29900;
        } else {
            if (!is_numeric($rawAmount)) {
                return response()->json([
                    'success' => false,
                    'error' => 'Amount must be an integer representing currency subunits (paise).',
                ], 400);
            }
            $amount = (int) $rawAmount;
        }

        // Minimum amount: 100 paise (₹1.00)
        if ($amount < 100) {
            return response()->json([
                'success' => false,
                'error' => 'Amount must be at least 100 paise (₹1.00).',
            ], 400);
        }

        $currency = strtoupper((string) $request->input('currency', 'INR'));
        $receipt = (string) ($request->input('receipt') ?: 'rcpt_' . bin2hex(random_bytes(6)));

        // 2. Optional storefront order creation
        $localOrder = null;
        if ($request->has('customer_email') || $request->has('coupon_code') || $request->boolean('create_order_record', true)) {
            try {
                $product = Product::where('active', true)->first();
                $localOrder = $this->orderService->createOrder([
                    'product_id' => $product ? $product->id : null,
                    'customer_name' => $request->input('customer_name', 'Customer'),
                    'customer_email' => $request->input('customer_email', 'customer@guest.local'),
                    'customer_phone' => $request->input('customer_phone'),
                    'customer_country' => $request->input('customer_country', 'IN'),
                    'coupon_code' => $request->input('coupon_code'),
                    'checkout_session_id' => $request->input('checkout_session_id'),
                    'payment_method' => 'razorpay',
                    'gateway' => 'razorpay',
                ]);

                if (empty($request->input('receipt'))) {
                    $receipt = $localOrder->order_number;
                }
                // If coupon applied, adjust amount to match discounted order price
                if ($request->input('amount') === null && $localOrder->total_amount > 0) {
                    $amount = (int) round($localOrder->total_amount * 100);
                }
            } catch (Exception $e) {
                Log::warning('Could not create pre-checkout local order record: ' . $e->getMessage());
            }
        }

        // 3. Call Razorpay API
        try {
            $notes = [
                'platform' => 'Laravel Standard Checkout',
            ];
            if ($localOrder) {
                $notes['order_uuid'] = $localOrder->uuid;
                $notes['order_number'] = $localOrder->order_number;
            }

            $orderData = $this->razorpayService->createRazorpayOrder(
                $amount,
                $currency,
                $receipt,
                $notes
            );

            if ($localOrder) {
                $localOrder->update([
                    'gateway_order_id' => $orderData['order_id'],
                ]);
            }

            // Return strictly required fields: order_id, amount, currency (plus key_id and local helpers)
            return response()->json([
                'success' => true,
                'order_id' => $orderData['order_id'],
                'amount' => $orderData['amount'],
                'currency' => $orderData['currency'],
                'receipt' => $orderData['receipt'] ?? $receipt,
                'key_id' => $this->razorpayService->getKeyId(),
                'local_order_id' => $localOrder?->id,
                'local_order_number' => $localOrder?->order_number,
                'uuid' => $localOrder?->uuid,
                'access_token' => $localOrder?->access_token,
            ], 200);

        } catch (Exception $e) {
            $statusCode = $e->getCode() === 401 ? 401 : ($e->getCode() === 400 ? 400 : 500);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], $statusCode);
        }
    }

    /**
     * Verify Razorpay Payment Signature
     *
     * Endpoint: POST /api/verify-payment
     * Algorithm: HMAC-SHA256(order_id + "|" + payment_id, KEY_SECRET)
     * Compare generated signature with razorpay_signature
     */
    public function verifyPayment(Request $request): JsonResponse
    {
        $orderId = (string) $request->input('razorpay_order_id', '');
        $paymentId = (string) $request->input('razorpay_payment_id', '');
        $signature = (string) $request->input('razorpay_signature', '');

        // Missing fields: return 400
        if (empty($orderId) || empty($paymentId) || empty($signature)) {
            return response()->json([
                'success' => false,
                'error' => 'Missing required fields: razorpay_order_id, razorpay_payment_id, and razorpay_signature are all required.',
            ], 400);
        }

        // Signature verification using HMAC-SHA256
        $isValid = $this->razorpayService->verifyPayment([
            'razorpay_order_id' => $orderId,
            'razorpay_payment_id' => $paymentId,
            'razorpay_signature' => $signature,
        ]);

        // Signature mismatch: return 400, do NOT mark as paid
        if (!$isValid) {
            Log::warning("Razorpay API Verification Failed: Signature mismatch for order {$orderId}, payment {$paymentId}");

            // If local order exists, mark failure
            $order = null;
            if ($request->has('order_id')) {
                $order = Order::find($request->input('order_id'));
            }
            if (!$order) {
                $order = Order::where('gateway_order_id', $orderId)->first();
            }
            if ($order && !$order->isPaid()) {
                $this->orderService->handlePaymentFailure(
                    $order,
                    'SIGNATURE_VERIFICATION_FAILED',
                    'Server signature does not match gateway payload.'
                );
            }

            return response()->json([
                'success' => false,
                'error' => 'Signature verification failed. Payment cannot be verified.',
            ], 400);
        }

        // Find corresponding local order to mark paid and fulfill
        $order = null;
        if ($request->has('order_id')) {
            $order = Order::find($request->input('order_id'));
        }
        if (!$order) {
            $order = Order::where('gateway_order_id', $orderId)->first();
        }

        if ($order && !$order->isPaid()) {
            $this->orderService->processSuccessfulPayment($order, [
                'gateway' => 'razorpay',
                'razorpay_order_id' => $orderId,
                'razorpay_payment_id' => $paymentId,
                'razorpay_signature' => $signature,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Payment verified successfully.',
            'order_id' => $order?->id,
            'order_number' => $order?->order_number,
            'uuid' => $order?->uuid,
            'access_token' => $order?->access_token,
            'redirect_url' => $order?->getSecureUrl(),
        ], 200);
    }
}
