<?php

namespace App\Http\Controllers;

use App\Services\Tracking\TrackingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TrackingController extends Controller
{
    public function __construct(
        protected TrackingService $trackingService
    ) {}

    public function track(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'event_name' => 'required|string|max:50',
            'event_id' => 'nullable|string|max:100',
            'order_id' => 'nullable|integer',
            'payload' => 'nullable|array',
        ]);

        $this->trackingService->trackEvent(
            $validated['event_name'],
            $validated['event_id'] ?? null,
            $validated['order_id'] ?? null,
            $validated['payload'] ?? []
        );

        return response()->json(['success' => true]);
    }
}
