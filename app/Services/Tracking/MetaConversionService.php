<?php

namespace App\Services\Tracking;

use App\Models\Order;
use App\Models\Setting;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MetaConversionService
{
    protected ?string $pixelId;
    protected ?string $accessToken;
    protected ?string $testEventCode;

    public function __construct()
    {
        $this->pixelId = Setting::get('meta_pixel_id') ?: env('META_PIXEL_ID');
        $this->accessToken = Setting::get('meta_access_token') ?: env('META_ACCESS_TOKEN');
        $this->testEventCode = Setting::get('meta_test_event_code') ?: env('META_TEST_EVENT_CODE');
    }

    public function isEnabled(): bool
    {
        return !empty($this->pixelId) && !empty($this->accessToken);
    }

    /**
     * Send server-side Purchase event to Meta Conversions API.
     */
    public function sendPurchaseEvent(Order $order): bool
    {
        if (!$this->isEnabled()) {
            return false;
        }

        $eventId = 'order-' . $order->id . '-purchase';

        $userData = [
            'client_ip_address' => $order->ip_address ?: request()->ip(),
            'client_user_agent' => $order->user_agent ?: request()->userAgent(),
        ];

        if (!empty($order->customer_email)) {
            $userData['em'] = [hash('sha256', strtolower(trim($order->customer_email)))];
        }

        if (!empty($order->customer_phone)) {
            $cleanPhone = preg_replace('/[^0-9]/', '', $order->customer_phone);
            $userData['ph'] = [hash('sha256', $cleanPhone)];
        }

        $customData = [
            'value' => (float) $order->total_amount,
            'currency' => $order->currency ?: 'INR',
            'content_name' => $order->product ? $order->product->name : \App\Models\Setting::get('product_name', 'CapCut Pro 1 Month'),
            'content_type' => 'product',
            'content_ids' => [$order->product ? $order->product->slug : 'capcut-pro-1-month'],
            'order_id' => $order->order_number,
        ];

        return $this->sendEvent('Purchase', $userData, $customData, $eventId, $order->getSecureUrl());
    }

    /**
     * Generic event dispatcher to Meta Graph API.
     */
    public function sendEvent(
        string $eventName,
        array $userData,
        array $customData = [],
        ?string $eventId = null,
        ?string $sourceUrl = null
    ): bool {
        if (!$this->isEnabled()) {
            return false;
        }

        $eventPayload = [
            'event_name' => $eventName,
            'event_time' => time(),
            'event_id' => $eventId,
            'event_source_url' => $sourceUrl ?: request()->fullUrl(),
            'action_source' => 'website',
            'user_data' => $userData,
            'custom_data' => $customData,
        ];

        $requestBody = [
            'data' => [$eventPayload],
        ];

        if (!empty($this->testEventCode)) {
            $requestBody['test_event_code'] = $this->testEventCode;
        }

        try {
            $url = "https://graph.facebook.com/v19.0/{$this->pixelId}/events";
            $response = Http::timeout(8)
                ->withToken($this->accessToken)
                ->post($url, $requestBody);

            if ($response->successful()) {
                Log::info("Meta Conversions API: Event '{$eventName}' sent successfully. Event ID: {$eventId}");
                return true;
            }

            Log::warning("Meta Conversions API Error: " . $response->body());
            return false;
        } catch (Exception $e) {
            Log::error("Meta Conversions API Exception: " . $e->getMessage());
            return false;
        }
    }
}
