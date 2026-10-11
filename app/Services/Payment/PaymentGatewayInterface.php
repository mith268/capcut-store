<?php

namespace App\Services\Payment;

use App\Models\Order;

interface PaymentGatewayInterface
{
    /**
     * Create an order on the payment gateway.
     *
     * @param Order $order
     * @return array Contains ['gateway_order_id' => string, 'amount' => float, 'currency' => string, 'key_id' => string]
     */
    public function createOrder(Order $order): array;

    /**
     * Verify payment signature returned from client.
     *
     * @param array $paymentData ['razorpay_order_id' => ..., 'razorpay_payment_id' => ..., 'razorpay_signature' => ...]
     * @return bool
     */
    public function verifyPayment(array $paymentData): bool;

    /**
     * Fetch payment status and details from gateway.
     *
     * @param string $paymentId
     * @return array
     */
    public function fetchPayment(string $paymentId): array;

    /**
     * Verify webhook signature.
     *
     * @param string $payload Raw request content
     * @param string $signature Signature from header
     * @return bool
     */
    public function verifyWebhookSignature(string $payload, string $signature): bool;
}
