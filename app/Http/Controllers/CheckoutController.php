<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\Setting;
use App\Services\Order\OrderService;
use App\Services\Payment\RazorpayPaymentService;
use App\Services\Tracking\TrackingService;
use App\Services\WhatsApp\WhatsAppService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CheckoutController extends Controller
{
    public function __construct(
        protected OrderService $orderService,
        protected RazorpayPaymentService $razorpayService,
        protected WhatsAppService $whatsappService,
        protected TrackingService $trackingService
    ) {}

    public function show(Request $request)
    {
        // Keep user on the landing page via inline checkout modal as required
        return redirect()->route('home', ['checkout' => 1]);
    }

    /**
     * AJAX live calculation of totals with coupon code validation.
     */
    public function calculate(Request $request): JsonResponse
    {
        $request->validate([
            'coupon_code' => 'nullable|string|max:50',
            'customer_email' => 'nullable|email|max:150',
        ]);

        $product = Product::where('active', true)->firstOrFail();

        $totals = $this->orderService->calculateCheckoutTotals(
            $product,
            $request->coupon_code,
            $request->customer_email
        );

        return response()->json([
            'success' => true,
            'unit_price' => $totals['unit_price'],
            'unit_price_formatted' => '₹' . number_format($totals['unit_price'], 2),
            'discount_amount' => $totals['discount_amount'],
            'discount_formatted' => '₹' . number_format($totals['discount_amount'], 2),
            'total_amount' => $totals['total_amount'],
            'total_formatted' => '₹' . number_format($totals['total_amount'], 2),
            'coupon_applied' => !is_null($totals['coupon']),
            'coupon_message' => $totals['coupon_message'],
        ]);
    }

    /**
     * Autosave customer draft data in real-time before payment attempt.
     */
    public function autosave(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'checkout_session_id' => 'nullable|string|max:100',
            'customer_name' => 'nullable|string|max:150',
            'customer_phone' => 'nullable|string|max:30',
            'customer_email' => 'nullable|string|max:150',
            'coupon_code' => 'nullable|string|max:50',
            'checkout_status' => 'nullable|string|in:draft,abandoned,payment_pending,payment_failed',
        ]);

        try {
            $order = $this->orderService->autosaveDraft($validated);

            return response()->json([
                'success' => true,
                'checkout_session_id' => $order->checkout_session_id,
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'status' => $order->checkout_status,
                'updated_at' => $order->updated_at->toIso8601String(),
            ]);
        } catch (Exception $e) {
            Log::warning('Autosave lead warning: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Unable to autosave draft.',
            ], 200);
        }
    }

    /**
     * Initiate checkout order and generate gateway order.
     * Frictionless: No upfront phone, address, or mandatory fields.
     */
    public function initiate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'customer_name' => 'nullable|string|max:100',
            'customer_email' => 'nullable|email:filter|max:150',
            'customer_phone' => 'nullable|string|max:20',
            'customer_country' => 'nullable|string|max:10',
            'coupon_code' => 'nullable|string|max:50',
            'payment_method' => 'nullable|string|in:razorpay,whatsapp',
            'checkout_session_id' => 'nullable|string|max:100',
            'terms' => 'nullable',
        ]);

        $product = Product::where('active', true)->firstOrFail();
        $validated['product_id'] = $product->id;
        $validated['customer_name'] = !empty($validated['customer_name']) ? $validated['customer_name'] : 'Customer';
        $validated['customer_email'] = !empty($validated['customer_email']) ? $validated['customer_email'] : 'customer@guest.local';
        $validated['payment_method'] = $validated['payment_method'] ?? 'razorpay';
        $validated['customer_country'] = $validated['customer_country'] ?? 'IN';

        try {
            // 1. Create order strictly with server-calculated price
            $order = $this->orderService->createOrder($validated);

            // 2. Create gateway order
            $gatewayOrder = $this->razorpayService->createOrder($order);

            // Log AddPaymentInfo tracking event
            $this->trackingService->trackEvent('AddPaymentInfo', null, $order->id, [
                'order_number' => $order->order_number,
                'amount' => (float) $order->total_amount,
            ]);

            return response()->json([
                'success' => true,
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'uuid' => $order->uuid,
                'access_token' => $order->access_token,
                'gateway_order_id' => $gatewayOrder['gateway_order_id'],
                'amount' => $gatewayOrder['amount'],
                'currency' => $gatewayOrder['currency'],
                'key_id' => $gatewayOrder['key_id'],
                'customer' => [
                    'name' => $order->customer_name,
                    'email' => $order->customer_email,
                    'contact' => $order->customer_phone,
                ],
                'is_mock' => $gatewayOrder['is_mock'] ?? false,
            ]);
        } catch (Exception $e) {
            Log::error('Checkout Initiation Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to initiate payment: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Claim or skip Canva Pro Bonus after successful payment.
     */
    public function claimCanva(Request $request): JsonResponse
    {
        $request->validate([
            'order_id' => 'required|integer',
            'canva_email' => 'nullable|email:filter|max:150',
            'skip' => 'nullable|boolean',
        ]);

        $order = Order::findOrFail($request->order_id);

        if (!$order->isPaid()) {
            return response()->json([
                'success' => false,
                'message' => 'Order must be confirmed paid before claiming Canva bonus.',
            ], 403);
        }

        $skip = $request->boolean('skip');
        if (!$skip && empty($request->canva_email)) {
            return response()->json([
                'success' => false,
                'message' => 'Please provide a valid email address for Canva Pro activation.',
            ], 422);
        }

        $result = $this->orderService->claimCanvaBonus(
            $order,
            $request->canva_email,
            $skip
        );

        return response()->json($result);
    }
}
