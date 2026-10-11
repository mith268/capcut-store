<?php

namespace App\Services\Tracking;

use App\Models\Order;
use App\Models\TrackingEvent;
use App\Models\UtmAttribution;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Session;

class TrackingService
{
    public function trackEvent(string $eventName, ?string $eventId = null, ?int $orderId = null, array $payload = []): TrackingEvent
    {
        // 1. Deduplication: if event_id is supplied, do not insert duplicate
        if (!empty($eventId)) {
            $existing = TrackingEvent::where('event_id', $eventId)->first();
            if ($existing) {
                return $existing;
            }
        }

        // 2. Security: filter out any sensitive credentials or secrets
        $sanitizedPayload = array_filter($payload, function ($key) {
            $lower = strtolower((string) $key);
            return !str_contains($lower, 'secret') &&
                   !str_contains($lower, 'password') &&
                   !str_contains($lower, 'token') &&
                   !str_contains($lower, 'key_secret') &&
                   !str_contains($lower, 'authorization');
        }, ARRAY_FILTER_USE_KEY);

        return TrackingEvent::create([
            'session_id' => Session::getId(),
            'event_name' => $eventName,
            'event_id' => $eventId,
            'order_id' => $orderId,
            'url' => Request::fullUrl(),
            'user_agent' => substr((string) Request::userAgent(), 0, 500),
            'ip_address' => Request::ip(),
            'payload' => $sanitizedPayload,
        ]);
    }

    public function captureUtm(Order $order, array $data): ?UtmAttribution
    {
        $hasData = !empty($data['utm_source']) ||
                   !empty($data['utm_campaign']) ||
                   !empty($data['fbclid']);

        if (!$hasData) {
            return null;
        }

        return UtmAttribution::create([
            'order_id' => $order->id,
            'session_id' => Session::getId(),
            'utm_source' => $data['utm_source'] ?? null,
            'utm_medium' => $data['utm_medium'] ?? null,
            'utm_campaign' => $data['utm_campaign'] ?? null,
            'utm_content' => $data['utm_content'] ?? null,
            'utm_term' => $data['utm_term'] ?? null,
            'fbclid' => $data['fbclid'] ?? null,
        ]);
    }
}
