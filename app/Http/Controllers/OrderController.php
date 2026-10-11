<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Order;
use App\Models\Setting;
use App\Services\Tracking\TrackingService;
use App\Services\WhatsApp\WhatsAppService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class OrderController extends Controller
{
    public function __construct(
        protected WhatsAppService $whatsappService,
        protected TrackingService $trackingService
    ) {}

    /**
     * Display secure customer order view.
     */
    public function show(string $uuid, string $token)
    {
        $order = Order::with(['product', 'payment', 'credential'])
            ->where('uuid', $uuid)
            ->where('access_token', $token)
            ->firstOrFail();

        $settings = Setting::getAllPublic();
        $supportWhatsappUrl = $this->whatsappService->generateSupportUrl($order->order_number);

        // Check if Meta Purchase event should fire on first view of verified paid order
        $shouldFireMetaPurchase = $order->isPaid() && !session()->has('fired_purchase_' . $order->id);
        if ($shouldFireMetaPurchase) {
            session()->put('fired_purchase_' . $order->id, true);
        }

        return view('order.show', compact('order', 'settings', 'supportWhatsappUrl', 'shouldFireMetaPurchase'));
    }

    /**
     * Secure credential reveal endpoint for customer.
     */
    public function reveal(Request $request, string $uuid, string $token): JsonResponse
    {
        // Rate limit reveals per IP to prevent rapid extraction
        $ip = $request->ip();
        $key = "reveal_creds:{$ip}:{$uuid}";

        if (RateLimiter::tooManyAttempts($key, 10)) {
            return response()->json([
                'success' => false,
                'message' => 'Too many requests. Please wait a moment.',
            ], 429);
        }

        RateLimiter::hit($key, 60);

        $order = Order::with('credential')
            ->where('uuid', $uuid)
            ->where('access_token', $token)
            ->first();

        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Order not found.'], 404);
        }

        if (!$order->canRevealCredentials()) {
            return response()->json([
                'success' => false,
                'message' => 'Access details are not ready yet. Please wait for payment verification or contact support.',
            ], 403);
        }

        $credential = $order->credential;

        if (!$order->credentials_revealed_at) {
            $order->update(['credentials_revealed_at' => now()]);
        }

        // Audit log the reveal WITHOUT recording the actual password/secret
        AuditLog::logAction(
            'customer_credential_revealed',
            'Order',
            $order->order_number,
            [
                'order_id' => $order->id,
                'revealed_at' => now()->toIso8601String(),
            ]
        );

        // Log tracking event
        $this->trackingService->trackEvent('CopyCredential', null, $order->id);

        return response()->json([
            'success' => true,
            'email' => $credential->email,
            'password' => $credential->password,
            'notes' => $credential->notes,
        ]);
    }

    /**
     * Self-service order lookup form.
     */
    public function lookupForm()
    {
        return view('order.lookup');
    }

    /**
     * Process self-service order lookup.
     */
    public function lookupProcess(Request $request)
    {
        // Rate limit order lookups (5 attempts per minute)
        $key = 'order_lookup:' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            return back()->with('error', 'Too many lookup attempts. Please try again after 1 minute.');
        }
        RateLimiter::hit($key, 60);

        $validated = $request->validate([
            'order_number' => 'required|string|max:50',
            'email' => 'required|email|max:150',
        ]);

        $order = Order::where('order_number', strtoupper(trim($validated['order_number'])))
            ->where('customer_email', strtolower(trim($validated['email'])))
            ->first();

        if (!$order) {
            return back()->with('error', 'No order found matching those credentials. Please check your order ID and email.');
        }

        return redirect()->to($order->getSecureUrl());
    }

    /**
     * Claim Canva Pro bonus from customer order portal.
     */
    public function claimCanvaBonus(Request $request, string $uuid, string $token): JsonResponse
    {
        $order = Order::where('uuid', $uuid)->where('access_token', $token)->firstOrFail();

        if (!$order->isPaid()) {
            return response()->json([
                'success' => false,
                'message' => 'Order must be confirmed paid before claiming Canva bonus.',
            ], 403);
        }

        $skip = $request->boolean('skip');
        if (!$skip) {
            $request->validate([
                'canva_email' => 'required|email:filter|max:150',
            ], [
                'canva_email.required' => 'Please enter an eligible email address for your Canva Pro bonus.',
                'canva_email.email' => 'Please enter a valid email address.',
            ]);
        }

        $result = app(\App\Services\Order\OrderService::class)->claimCanvaBonus(
            $order,
            $request->input('canva_email'),
            $skip
        );

        return response()->json($result);
    }
}
