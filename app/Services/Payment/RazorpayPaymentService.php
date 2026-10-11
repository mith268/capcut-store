<?php

namespace App\Services\Payment;

use App\Models\Order;
use App\Models\Setting;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RazorpayPaymentService implements PaymentGatewayInterface
{
    protected string $keyId;
    protected string $keySecret;
    protected string $webhookSecret;
    protected string $baseUrl = 'https://api.razorpay.com/v1';

    public function getMode(): string
    {
        return (string) Setting::get('razorpay_mode', 'test');
    }

    public function isLiveMode(): bool
    {
        return $this->getMode() === 'live';
    }

    public function getKeyId(): string
    {
        $dbKey = Setting::get('razorpay_key_id');
        if (!empty($dbKey) && $dbKey !== 'rzp_test_placeholder') {
            return (string) $dbKey;
        }

        $envKey = config('services.razorpay.key_id') ?: env('RAZORPAY_KEY_ID', '');
        return ($envKey && $envKey !== 'rzp_test_placeholder') ? (string) $envKey : '';
    }

    public function getKeySecret(): string
    {
        $dbSecret = Setting::get('razorpay_key_secret');
        if (!empty($dbSecret) && $dbSecret !== 'test_secret_placeholder') {
            return (string) $dbSecret;
        }

        return (string) (config('services.razorpay.key_secret') ?: env('RAZORPAY_KEY_SECRET', ''));
    }

    public function getWebhookSecret(): string
    {
        $dbWebhook = Setting::get('razorpay_webhook_secret');
        if (!empty($dbWebhook)) {
            return (string) $dbWebhook;
        }

        return (string) (config('services.razorpay.webhook_secret') ?: env('RAZORPAY_WEBHOOK_SECRET', ''));
    }

    public function isConfigured(): bool
    {
        $keyId = $this->getKeyId();
        $keySecret = $this->getKeySecret();
        return !empty($keyId) && !empty($keySecret) && $keyId !== 'rzp_test_placeholder';
    }

    /**
     * Test connection to Razorpay using provided or configured credentials
     *
     * @param string|null $keyId
     * @param string|null $keySecret
     * @return array
     */
    public function testConnection(?string $keyId = null, ?string $keySecret = null): array
    {
        $keyId = $keyId ?: $this->getKeyId();
        $keySecret = $keySecret ?: $this->getKeySecret();
        $mode = $this->getMode();
        $modeLabel = $mode === 'live' ? 'Live Mode' : 'Test Mode';

        if (empty($keyId) || empty($keySecret) || $keyId === 'rzp_test_placeholder') {
            return [
                'success' => false,
                'message' => 'Razorpay connection failed: Key ID and Key Secret must both be provided in Admin settings.',
                'mode' => $mode,
            ];
        }

        // Mode and prefix sanity verification
        if ($mode === 'test' && !str_starts_with($keyId, 'rzp_test_')) {
            return [
                'success' => false,
                'message' => "Configuration warning: In Test Mode, Razorpay Key ID should start with 'rzp_test_'. Currently starts with '" . substr($keyId, 0, 8) . "'.",
                'mode' => $mode,
            ];
        }

        if ($mode === 'live' && !str_starts_with($keyId, 'rzp_live_')) {
            return [
                'success' => false,
                'message' => "Configuration warning: In Live Mode, Razorpay Key ID should start with 'rzp_live_'. Currently starts with '" . substr($keyId, 0, 8) . "'.",
                'mode' => $mode,
            ];
        }

        try {
            $response = Http::withBasicAuth($keyId, $keySecret)
                ->withOptions(['verify' => $this->getCaVerifyOption()])
                ->timeout(12)
                ->get("{$this->baseUrl}/orders", [
                    'count' => 1,
                ]);

            if ($response->status() === 401) {
                return [
                    'success' => false,
                    'message' => 'Razorpay connection failed: Invalid credentials (HTTP 401 Unauthorized). Please check your Key ID and Key Secret.',
                    'mode' => $mode,
                ];
            }

            if ($response->successful()) {
                return [
                    'success' => true,
                    'message' => "Razorpay {$modeLabel} Connected Successfully",
                    'mode' => $mode,
                ];
            }

            $desc = $response->json('error.description') ?? "API responded with HTTP {$response->status()}";
            return [
                'success' => false,
                'message' => "Razorpay connection failed: {$desc}",
                'mode' => $mode,
            ];
        } catch (Exception $e) {
            Log::error('Razorpay testConnection failed: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Razorpay connection failed: Network error or connection timed out.',
                'mode' => $mode,
            ];
        }
    }

    /**
     * Create raw order with Razorpay API (POST https://api.razorpay.com/v1/orders)
     *
     * @param int $amountInPaise Minimum amount: 100 paise
     * @param string $currency
     * @param string|null $receipt
     * @param array $notes
     * @return array
     * @throws Exception
     */
    public function createRazorpayOrder(int $amountInPaise, string $currency = 'INR', ?string $receipt = null, array $notes = []): array
    {
        if ($amountInPaise < 100) {
            throw new Exception('Amount must be at least 100 paise (₹1.00).', 400);
        }

        $currency = strtoupper($currency ?: 'INR');
        $receipt = (string) ($receipt ?: 'rcpt_' . bin2hex(random_bytes(6)));

        if (!$this->isConfigured()) {
            $mockOrderId = 'order_mock_' . bin2hex(random_bytes(8));
            Log::info("Razorpay in mock mode: generated simulated order {$mockOrderId}");
            return [
                'order_id' => $mockOrderId,
                'gateway_order_id' => $mockOrderId,
                'amount' => $amountInPaise,
                'currency' => $currency,
                'receipt' => $receipt,
                'key_id' => $this->getKeyId() ?: 'rzp_test_placeholder',
                'is_mock' => true,
            ];
        }

        try {
            $response = Http::withBasicAuth($this->getKeyId(), $this->getKeySecret())
                ->withOptions(['verify' => $this->getCaVerifyOption()])
                ->timeout(15)
                ->post("{$this->baseUrl}/orders", [
                    'amount' => $amountInPaise,
                    'currency' => $currency,
                    'receipt' => $receipt,
                    'notes' => $notes,
                ]);

            if ($response->status() === 401) {
                Log::error('Razorpay Auth Failure (401): invalid credentials');
                throw new Exception('Razorpay authentication failed. Invalid Key ID or Key Secret.', 401);
            }

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'order_id' => $data['id'],
                    'gateway_order_id' => $data['id'],
                    'amount' => (int) $data['amount'],
                    'currency' => $data['currency'],
                    'receipt' => $data['receipt'] ?? $receipt,
                    'key_id' => $this->getKeyId(),
                    'is_mock' => false,
                ];
            }

            $errorMessage = $response->json('error.description') ?? 'Razorpay API returned status ' . $response->status();
            Log::error('Razorpay Order Creation Failed: ' . $errorMessage, [
                'status' => $response->status(),
                'response' => $response->json(),
            ]);

            throw new Exception('Razorpay order creation failed: ' . $errorMessage, 500);
        } catch (Exception $e) {
            if ($e->getCode() === 401 || $e->getCode() === 400) {
                throw $e;
            }
            Log::error('Razorpay Order Exception: ' . $e->getMessage());
            throw new Exception($e->getMessage(), $e->getCode() ?: 500);
        }
    }

    public function createOrder(Order $order): array
    {
        $amountInPaise = (int) round($order->total_amount * 100);

        return $this->createRazorpayOrder(
            $amountInPaise,
            $order->currency ?: 'INR',
            $order->order_number,
            [
                'order_uuid' => $order->uuid,
                'order_number' => $order->order_number,
                'customer_name' => $order->customer_name,
                'customer_email' => $order->customer_email,
            ]
        );
    }

    public function verifyPayment(array $paymentData): bool
    {
        $orderId = $paymentData['razorpay_order_id'] ?? null;
        $paymentId = $paymentData['razorpay_payment_id'] ?? null;
        $signature = $paymentData['razorpay_signature'] ?? null;

        if (!$orderId || !$paymentId || !$signature) {
            return false;
        }

        // Mock mode verification for testing environment
        if (!$this->isConfigured() || str_starts_with($orderId, 'order_mock_')) {
            return hash_equals(hash_hmac('sha256', $orderId . '|' . $paymentId, 'test_mock_secret'), $signature) ||
                   $signature === 'mock_valid_signature';
        }

        $expectedSignature = hash_hmac('sha256', $orderId . '|' . $paymentId, $this->getKeySecret());

        return hash_equals($expectedSignature, $signature);
    }

    public function fetchPayment(string $paymentId): array
    {
        if (!$this->isConfigured() || str_starts_with($paymentId, 'pay_mock_')) {
            return [
                'id' => $paymentId,
                'status' => 'captured',
                'amount' => 29900,
                'currency' => 'INR',
                'method' => 'upi',
            ];
        }

        $response = Http::withBasicAuth($this->getKeyId(), $this->getKeySecret())
            ->withOptions(['verify' => $this->getCaVerifyOption()])
            ->timeout(10)
            ->get("{$this->baseUrl}/payments/{$paymentId}");

        if ($response->successful()) {
            return $response->json();
        }

        throw new Exception('Unable to fetch payment details from Razorpay: ' . $response->body());
    }

    public function verifyWebhookSignature(string $payload, string $signature): bool
    {
        $webhookSecret = $this->getWebhookSecret();
        if (empty($webhookSecret) || empty($signature)) {
            return false;
        }

        $expectedSignature = hash_hmac('sha256', $payload, $webhookSecret);

        return hash_equals($expectedSignature, $signature);
    }

    protected function getCaVerifyOption(): string|bool
    {
        if (ini_get('curl.cainfo') && file_exists((string) ini_get('curl.cainfo'))) {
            return (string) ini_get('curl.cainfo');
        }

        if (file_exists('/etc/ssl/cert.pem')) {
            return '/etc/ssl/cert.pem';
        }

        return true;
    }
}
