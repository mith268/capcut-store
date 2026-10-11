<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\Order\OrderService;
use App\Services\Payment\RazorpayPaymentService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function __construct(
        protected RazorpayPaymentService $razorpayService,
        protected OrderService $orderService
    ) {}

    /**
     * Server-side signature verification and payment capture handler.
     */
    public function verify(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'order_id' => 'nullable|integer',
            'razorpay_order_id' => 'required|string',
            'razorpay_payment_id' => 'required|string',
            'razorpay_signature' => 'required|string',
        ]);

        $order = null;
        if (!empty($validated['order_id'])) {
            $order = Order::find($validated['order_id']);
        }
        if (!$order) {
            $order = Order::where('gateway_order_id', $validated['razorpay_order_id'])->first();
        }

        if (!$order) {
            return response()->json([
                'success' => false,
                'error' => 'Order not found for specified Razorpay order ID.',
            ], 404);
        }

        // Check if order is already paid to prevent duplicate processing
        if ($order->isPaid()) {
            return response()->json([
                'success' => true,
                'message' => 'Payment already verified.',
                'redirect_url' => $order->getSecureUrl(),
            ]);
        }

        // Verify cryptographic signature server-side
        $isValidSignature = $this->razorpayService->verifyPayment($validated);

        if (!$isValidSignature) {
            Log::warning("Payment signature verification failed for Order #{$order->order_number}", [
                'received' => $validated,
            ]);

            $this->orderService->handlePaymentFailure(
                $order,
                'SIGNATURE_VERIFICATION_FAILED',
                'Server signature did not match gateway payload.'
            );

            return response()->json([
                'success' => false,
                'error' => 'Payment signature verification failed. Please contact support if your account was charged.',
                'message' => 'Payment signature verification failed. Please contact support if your account was charged.',
            ], 400);
        }

        try {
            // Optional: Fetch live payment details from gateway to verify status
            $gatewayPayment = $this->razorpayService->fetchPayment($validated['razorpay_payment_id']);

            $paymentData = [
                'gateway' => 'razorpay',
                'razorpay_order_id' => $validated['razorpay_order_id'],
                'razorpay_payment_id' => $validated['razorpay_payment_id'],
                'razorpay_signature' => $validated['razorpay_signature'],
                'raw_response' => $gatewayPayment,
            ];

            $this->orderService->processSuccessfulPayment($order, $paymentData);

            return response()->json([
                'success' => true,
                'message' => 'Payment verified successfully.',
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'uuid' => $order->uuid,
                'access_token' => $order->access_token,
                'redirect_url' => $order->getSecureUrl(),
                'canva_bonus_eligible' => true,
            ]);
        } catch (Exception $e) {
            Log::error("Payment processing error for Order #{$order->order_number}: " . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while finalizing your order. Please refresh your order page shortly.',
                'redirect_url' => $order->getSecureUrl(),
            ], 500);
        }
    }

    /**
     * Handle payment dismissal or failure reported by frontend SDK.
     */
    public function failure(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'order_id' => 'required|integer',
            'error_code' => 'nullable|string',
            'error_description' => 'nullable|string',
        ]);

        $order = Order::find($validated['order_id']);

        if ($order && !$order->isPaid()) {
            $this->orderService->handlePaymentFailure(
                $order,
                $validated['error_code'] ?? 'CHECKOUT_DISMISSED',
                $validated['error_description'] ?? 'Payment was cancelled or dismissed by customer.'
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Payment status updated.',
        ]);
    }
}
