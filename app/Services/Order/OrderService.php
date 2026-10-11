<?php

namespace App\Services\Order;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Services\Coupon\CouponService;
use App\Services\Delivery\DeliveryService;
use App\Services\Inventory\InventoryService;
use App\Services\Tracking\MetaConversionService;
use App\Services\Tracking\TrackingService;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderService
{
    public function __construct(
        protected CouponService $couponService,
        protected InventoryService $inventoryService,
        protected DeliveryService $deliveryService,
        protected TrackingService $trackingService,
        protected MetaConversionService $metaService
    ) {}

    /**
     * Calculate order totals STRICTLY server-side.
     * The client price is ignored completely.
     */
    public function calculateCheckoutTotals(Product $product, ?string $couponCode = null, ?string $customerEmail = null): array
    {
        $unitPrice = (float) $product->effective_price;
        $quantity = 1;
        $subtotal = round($unitPrice * $quantity, 2);

        $discount = 0.00;
        $coupon = null;
        $couponMessage = null;

        if (!empty($couponCode)) {
            $couponResult = $this->couponService->validateAndCalculate($couponCode, $subtotal, $customerEmail);
            if ($couponResult['valid']) {
                $discount = $couponResult['discount'];
                $coupon = $couponResult['coupon'];
                $couponMessage = $couponResult['message'];
            } else {
                $couponMessage = $couponResult['message'];
            }
        }

        $total = max(0.00, round($subtotal - $discount, 2));

        return [
            'unit_price' => $unitPrice,
            'quantity' => $quantity,
            'subtotal' => $subtotal,
            'discount_amount' => $discount,
            'total_amount' => $total,
            'coupon' => $coupon,
            'coupon_message' => $couponMessage,
        ];
    }

    /**
     * Autosave customer draft or lead without strict required field checks.
     */
    public function autosaveDraft(array $data): Order
    {
        $sessionId = $data['checkout_session_id'] ?? null;
        $order = null;

        if (!empty($sessionId)) {
            $order = Order::where('checkout_session_id', $sessionId)->first();
        }

        // If order exists and is already paid, never revert to draft
        if ($order && $order->isPaid()) {
            return $order;
        }

        $product = Product::where('active', true)->firstOrFail();
        $totals = $this->calculateCheckoutTotals(
            $product,
            $data['coupon_code'] ?? null,
            $data['customer_email'] ?? null
        );

        $customerName = !empty($data['customer_name']) ? trim($data['customer_name']) : null;
        $customerPhone = !empty($data['customer_phone']) ? trim($data['customer_phone']) : null;
        $customerEmail = !empty($data['customer_email']) ? strtolower(trim($data['customer_email'])) : null;
        $checkoutStatus = $data['checkout_status'] ?? 'draft';

        $payload = [
            'product_id' => $product->id,
            'quantity' => $totals['quantity'],
            'unit_price' => $totals['unit_price'],
            'discount_amount' => $totals['discount_amount'],
            'coupon_id' => $totals['coupon'] ? $totals['coupon']->id : null,
            'total_amount' => $totals['total_amount'],
            'currency' => $product->currency ?? 'INR',
            'last_activity_at' => now(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ];

        if ($customerName !== null) $payload['customer_name'] = $customerName;
        if ($customerPhone !== null) $payload['customer_phone'] = $customerPhone;
        if ($customerEmail !== null) $payload['customer_email'] = $customerEmail;

        if ($order) {
            if ($checkoutStatus === 'abandoned' && !$order->isPaid()) {
                $payload['checkout_status'] = 'abandoned';
                $payload['abandoned_at'] = now();
            } elseif ($order->checkout_status === 'draft') {
                $payload['checkout_status'] = $checkoutStatus;
            }
            $order->update($payload);
        } else {
            $payload['checkout_session_id'] = $sessionId ?: (string) \Illuminate\Support\Str::uuid();
            $payload['checkout_status'] = $checkoutStatus;
            $payload['payment_status'] = 'pending';
            $payload['delivery_status'] = 'pending';
            $payload['fulfillment_status'] = 'pending';
            $payload['canva_bonus_status'] = 'not_requested';
            $order = Order::create($payload);

            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'product_name' => $product->name,
                'unit_price' => $totals['unit_price'],
                'quantity' => $totals['quantity'],
                'subtotal' => $totals['subtotal'],
            ]);
        }

        return $order;
    }

    /**
     * Create or update order in pending state with server-calculated price.
     */
    public function createOrder(array $validatedData): Order
    {
        $product = Product::findOrFail($validatedData['product_id']);

        $totals = $this->calculateCheckoutTotals(
            $product,
            $validatedData['coupon_code'] ?? null,
            $validatedData['customer_email'] ?? null
        );

        return DB::transaction(function () use ($validatedData, $product, $totals) {
            $sessionId = $validatedData['checkout_session_id'] ?? null;
            $order = null;

            if (!empty($sessionId)) {
                $order = Order::where('checkout_session_id', $sessionId)->first();
            }

            $orderPayload = [
                'customer_name' => !empty($validatedData['customer_name']) ? trim($validatedData['customer_name']) : 'Customer',
                'customer_email' => !empty($validatedData['customer_email']) ? strtolower(trim($validatedData['customer_email'])) : 'customer@guest.local',
                'customer_phone' => trim($validatedData['customer_phone'] ?? ''),
                'customer_country' => $validatedData['customer_country'] ?? 'IN',
                'product_id' => $product->id,
                'quantity' => $totals['quantity'],
                'unit_price' => $totals['unit_price'],
                'discount_amount' => $totals['discount_amount'],
                'coupon_id' => $totals['coupon'] ? $totals['coupon']->id : null,
                'total_amount' => $totals['total_amount'],
                'currency' => $product->currency,
                'payment_method' => $validatedData['payment_method'] ?? 'razorpay',
                'gateway' => $validatedData['gateway'] ?? 'razorpay',
                'payment_status' => 'pending',
                'checkout_status' => 'payment_pending',
                'payment_attempted_at' => now(),
                'last_activity_at' => now(),
                'delivery_status' => 'pending',
                'fulfillment_status' => 'pending',
                'canva_bonus_status' => 'not_requested',
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'utm_source' => request()->cookie('utm_source') ?: request()->get('utm_source'),
                'utm_medium' => request()->cookie('utm_medium') ?: request()->get('utm_medium'),
                'utm_campaign' => request()->cookie('utm_campaign') ?: request()->get('utm_campaign'),
                'utm_content' => request()->cookie('utm_content') ?: request()->get('utm_content'),
                'utm_term' => request()->cookie('utm_term') ?: request()->get('utm_term'),
                'fbp' => request()->cookie('_fbp') ?: request()->get('_fbp'),
                'fbc' => request()->cookie('_fbc') ?: request()->get('_fbc'),
            ];

            if ($order) {
                $order->update($orderPayload);
                if (!$order->items()->exists()) {
                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'unit_price' => $totals['unit_price'],
                        'quantity' => $totals['quantity'],
                        'subtotal' => $totals['subtotal'],
                    ]);
                }
            } else {
                $orderPayload['checkout_session_id'] = $sessionId ?: (string) \Illuminate\Support\Str::uuid();
                $order = Order::create($orderPayload);

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'unit_price' => $totals['unit_price'],
                    'quantity' => $totals['quantity'],
                    'subtotal' => $totals['subtotal'],
                ]);
            }

            // Save UTM attribution if present
            $this->trackingService->captureUtm($order, [
                'utm_source' => request()->cookie('utm_source') ?: request()->get('utm_source'),
                'utm_medium' => request()->cookie('utm_medium') ?: request()->get('utm_medium'),
                'utm_campaign' => request()->cookie('utm_campaign') ?: request()->get('utm_campaign'),
                'utm_content' => request()->cookie('utm_content') ?: request()->get('utm_content'),
                'utm_term' => request()->cookie('utm_term') ?: request()->get('utm_term'),
                'fbclid' => request()->cookie('fbclid') ?: request()->get('fbclid'),
            ]);

            return $order;
        });
    }

    /**
     * Complete payment, record payment record, allocate inventory, trigger delivery.
     */
    public function processSuccessfulPayment(Order $order, array $paymentData): bool
    {
        return DB::transaction(function () use ($order, $paymentData) {
            // Check if already paid to ensure idempotency
            if ($order->isPaid()) {
                Log::info("Order #{$order->order_number} is already paid. Skipping redundant processing.");
                return true;
            }

            // Create or update payment record
            Payment::updateOrCreate(
                ['order_id' => $order->id],
                [
                    'gateway' => $paymentData['gateway'] ?? 'razorpay',
                    'gateway_order_id' => $paymentData['razorpay_order_id'] ?? null,
                    'gateway_payment_id' => $paymentData['razorpay_payment_id'] ?? null,
                    'gateway_signature' => $paymentData['razorpay_signature'] ?? null,
                    'amount' => $order->total_amount,
                    'currency' => $order->currency,
                    'status' => 'captured',
                    'raw_response' => $paymentData['raw_response'] ?? null,
                ]
            );

            // Update order status
            $order->update([
                'payment_status' => 'paid',
                'checkout_status' => 'completed',
                'payment_method' => $paymentData['gateway'] ?? 'razorpay',
                'gateway' => $paymentData['gateway'] ?? 'razorpay',
                'gateway_order_id' => $paymentData['razorpay_order_id'] ?? null,
                'gateway_payment_id' => $paymentData['razorpay_payment_id'] ?? null,
                'fulfillment_status' => 'delivered',
                'paid_at' => now(),
                'last_activity_at' => now(),
            ]);

            // Record coupon usage if applicable
            if ($order->coupon_id && $order->coupon) {
                $this->couponService->recordRedemption(
                    $order->coupon,
                    $order,
                    $order->customer_email,
                    (float) $order->discount_amount
                );
            }

            // Allocate inventory credential
            $credential = $this->inventoryService->allocateCredential($order);

            // Trigger digital delivery
            $this->deliveryService->deliverOrder($order);

            // Trigger Meta Conversions API Server Event
            $this->metaService->sendPurchaseEvent($order);

            // Log event in database tracking
            $this->trackingService->trackEvent(
                'Purchase',
                'order-' . $order->id . '-purchase',
                $order->id,
                [
                    'value' => (float) $order->total_amount,
                    'currency' => $order->currency,
                    'order_number' => $order->order_number,
                ]
            );

            return true;
        });
    }

    /**
     * Handle payment failure.
     */
    public function handlePaymentFailure(Order $order, string $errorCode, string $errorDesc): void
    {
        Payment::updateOrCreate(
            ['order_id' => $order->id],
            [
                'gateway' => 'razorpay',
                'amount' => $order->total_amount,
                'currency' => $order->currency,
                'status' => 'failed',
                'error_code' => $errorCode,
                'error_description' => $errorDesc,
            ]
        );

        $isDismissed = in_array($errorCode, ['MODAL_DISMISSED', 'CHECKOUT_DISMISSED']);
        $order->update([
            'payment_status' => $isDismissed ? 'pending' : 'failed',
            'checkout_status' => $isDismissed ? 'abandoned' : 'payment_failed',
            'abandoned_at' => $isDismissed ? now() : $order->abandoned_at,
            'last_activity_at' => now(),
        ]);
    }

    /**
     * Claim or skip Canva Pro Bonus for paid order.
     */
    public function claimCanvaBonus(Order $order, ?string $email, bool $skip = false): array
    {
        if ($skip) {
            $order->update([
                'canva_bonus_status' => 'skipped',
            ]);

            return [
                'success' => true,
                'status' => 'skipped',
                'message' => 'Canva Pro bonus skipped. You can claim it later anytime through your order reference.',
            ];
        }

        $email = strtolower(trim((string) $email));
        $order->update([
            'canva_email' => $email,
            'canva_bonus_status' => 'requested',
        ]);

        $this->trackingService->trackEvent(
            'CanvaBonusRequested',
            'order-' . $order->id . '-canva',
            $order->id,
            [
                'email' => $email,
                'order_number' => $order->order_number,
            ]
        );

        return [
            'success' => true,
            'status' => 'requested',
            'message' => 'Canva Pro bonus claimed! Activation will be provided to ' . $email . '.',
        ];
    }
}
