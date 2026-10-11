<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentEvent;
use App\Services\Order\OrderService;
use App\Services\Payment\RazorpayPaymentService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function __construct(
        protected RazorpayPaymentService $razorpayService,
        protected OrderService $orderService
    ) {}

    /**
     * Handle incoming webhooks from Razorpay with signature verification and idempotency.
     */
    public function handleRazorpay(Request $request): JsonResponse
    {
        $payload = $request->getContent();
        $signature = $request->header('X-Razorpay-Signature') ?? '';

        // Verify webhook signature if configured
        if ($this->razorpayService->isConfigured()) {
            if (!$this->razorpayService->verifyWebhookSignature($payload, $signature)) {
                Log::warning('Razorpay Webhook: Invalid signature received', [
                    'signature' => $signature,
                ]);
                return response()->json(['error' => 'Invalid signature'], 400);
            }
        }

        $data = json_decode($payload, true);

        if (!$data || !isset($data['event'])) {
            return response()->json(['error' => 'Invalid payload'], 400);
        }

        $eventId = $data['id'] ?? ('event_' . md5($payload));
        $eventType = $data['event'];

        // Idempotency Check: Prevent duplicate event processing
        $existingEvent = PaymentEvent::where('gateway', 'razorpay')
            ->where('event_id', $eventId)
            ->first();

        if ($existingEvent && $existingEvent->processed) {
            Log::info("Razorpay Webhook: Event {$eventId} already processed. Skipping.");
            return response()->json(['status' => 'already_processed'], 200);
        }

        if (!$existingEvent) {
            $paymentEvent = PaymentEvent::create([
                'gateway' => 'razorpay',
                'event_id' => $eventId,
                'event_type' => $eventType,
                'payload' => $data,
                'signature' => $signature,
                'processed' => false,
            ]);
        } else {
            $paymentEvent = $existingEvent;
        }

        try {
            switch ($eventType) {
                case 'payment.captured':
                case 'order.paid':
                    $this->handlePaymentCaptured($data);
                    break;

                case 'payment.failed':
                    $this->handlePaymentFailed($data);
                    break;

                case 'refund.processed':
                    $this->handleRefundProcessed($data);
                    break;

                default:
                    Log::info("Razorpay Webhook: Unhandled event type '{$eventType}'");
                    break;
            }

            $paymentEvent->update([
                'processed' => true,
                'processed_at' => now(),
            ]);

            return response()->json(['status' => 'success'], 200);
        } catch (Exception $e) {
            Log::error("Razorpay Webhook Processing Error [Event: {$eventId}]: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['error' => 'Processing error: ' . $e->getMessage()], 500);
        }
    }

    protected function handlePaymentCaptured(array $data): void
    {
        $paymentEntity = $data['payload']['payment']['entity'] ?? [];
        $orderId = $paymentEntity['order_id'] ?? null;
        $paymentId = $paymentEntity['id'] ?? null;

        if (!$orderId && !$paymentId) {
            return;
        }

        // Locate order via gateway_order_id in payments or receipt in notes
        $order = null;
        if ($orderId) {
            $payment = Payment::where('gateway_order_id', $orderId)->first();
            if ($payment) {
                $order = $payment->order;
            }
        }

        if (!$order && isset($paymentEntity['notes']['order_number'])) {
            $order = Order::where('order_number', $paymentEntity['notes']['order_number'])->first();
        }

        if (!$order && isset($paymentEntity['notes']['order_uuid'])) {
            $order = Order::where('uuid', $paymentEntity['notes']['order_uuid'])->first();
        }

        if (!$order) {
            Log::warning("Razorpay Webhook: Order not found for gateway order {$orderId}");
            return;
        }

        if ($order->isPaid()) {
            Log::info("Razorpay Webhook: Order #{$order->order_number} is already marked as paid.");
            return;
        }

        $this->orderService->processSuccessfulPayment($order, [
            'gateway' => 'razorpay',
            'razorpay_order_id' => $orderId,
            'razorpay_payment_id' => $paymentId,
            'razorpay_signature' => 'webhook_verified',
            'raw_response' => $paymentEntity,
        ]);
    }

    protected function handlePaymentFailed(array $data): void
    {
        $paymentEntity = $data['payload']['payment']['entity'] ?? [];
        $orderId = $paymentEntity['order_id'] ?? null;

        $order = null;
        if ($orderId) {
            $payment = Payment::where('gateway_order_id', $orderId)->first();
            if ($payment) {
                $order = $payment->order;
            }
        }

        if ($order && !$order->isPaid()) {
            $this->orderService->handlePaymentFailure(
                $order,
                $paymentEntity['error_code'] ?? 'PAYMENT_FAILED_WEBHOOK',
                $paymentEntity['error_description'] ?? 'Payment failed according to gateway webhook.'
            );
        }
    }

    protected function handleRefundProcessed(array $data): void
    {
        $refundEntity = $data['payload']['refund']['entity'] ?? [];
        $paymentId = $refundEntity['payment_id'] ?? null;

        if (!$paymentId) {
            return;
        }

        $payment = Payment::where('gateway_payment_id', $paymentId)->first();
        if ($payment) {
            $payment->update(['status' => 'refunded']);
            $order = $payment->order;
            if ($order) {
                $order->update(['payment_status' => 'refunded']);
            }
        }
    }
}
