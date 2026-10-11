<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Order;
use App\Models\Payment;
use App\Services\Delivery\DeliveryService;
use App\Services\Inventory\InventoryService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(
        protected InventoryService $inventoryService,
        protected DeliveryService $deliveryService
    ) {}

    public function index(Request $request)
    {
        $status = $request->query('status');
        $search = $request->query('search') ?: $request->query('q');

        $query = Order::with(['product', 'payment', 'credential'])->latest();

        if ($status) {
            if ($status === 'paid') {
                $query->whereIn('payment_status', ['paid', 'delivered']);
            } elseif ($status === 'draft') {
                $query->where('checkout_status', 'draft');
            } elseif ($status === 'abandoned') {
                $query->where('checkout_status', 'abandoned');
            } elseif ($status === 'pending') {
                $query->where(function ($q) {
                    $q->where('checkout_status', 'payment_pending')
                      ->orWhere(function ($sub) {
                          $sub->where('payment_status', 'pending')
                              ->whereNotIn('checkout_status', ['draft', 'abandoned']);
                      });
                });
            } elseif ($status === 'failed') {
                $query->where(function ($q) {
                    $q->where('payment_status', 'failed')
                      ->orWhere('checkout_status', 'payment_failed');
                });
            } elseif ($status === 'refunded') {
                $query->where('payment_status', 'refunded');
            } else {
                $query->where('payment_status', $status);
            }
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%")
                  ->orWhere('customer_email', 'like', "%{$search}%")
                  ->orWhere('customer_phone', 'like', "%{$search}%")
                  ->orWhere('gateway_order_id', 'like', "%{$search}%")
                  ->orWhere('gateway_payment_id', 'like', "%{$search}%")
                  ->orWhere('checkout_session_id', 'like', "%{$search}%");
            });
        }

        $orders = $query->paginate(20)->withQueryString();

        $counts = [
            'all' => Order::count(),
            'paid' => Order::whereIn('payment_status', ['paid', 'delivered'])->count(),
            'draft' => Order::where('checkout_status', 'draft')->count(),
            'abandoned' => Order::where('checkout_status', 'abandoned')->count(),
            'pending' => Order::where(function ($q) {
                $q->where('checkout_status', 'payment_pending')
                  ->orWhere(function ($sub) {
                      $sub->where('payment_status', 'pending')
                          ->whereNotIn('checkout_status', ['draft', 'abandoned']);
                  });
            })->count(),
            'failed' => Order::where('payment_status', 'failed')->orWhere('checkout_status', 'payment_failed')->count(),
            'refunded' => Order::where('payment_status', 'refunded')->count(),
        ];

        return view('admin.orders.index', compact('orders', 'counts', 'status', 'search'));
    }

    public function show(Order $order)
    {
        $order->load(['product', 'items', 'payment', 'payments', 'credential', 'coupon', 'utmAttribution']);

        $auditLogs = AuditLog::where(function ($q) use ($order) {
            $q->where('entity_id', $order->order_number)
              ->orWhere('entity_id', (string) $order->id);
        })->latest()->get();

        return view('admin.orders.show', compact('order', 'auditLogs'));
    }

    public function markAsPaid(Request $request, Order $order)
    {
        $request->validate([
            'reason' => 'required|string|min:5|max:255',
        ]);

        if ($order->isPaid()) {
            return back()->with('error', 'Order is already marked as paid.');
        }

        $order->update([
            'payment_status' => 'paid',
            'paid_at' => now(),
        ]);

        Payment::updateOrCreate(
            ['order_id' => $order->id],
            [
                'gateway' => 'manual_admin',
                'amount' => $order->total_amount,
                'currency' => $order->currency,
                'status' => 'captured',
            ]
        );

        // Attempt allocation
        $this->inventoryService->allocateCredential($order);

        AuditLog::logAction('order_marked_paid_manually', 'Order', $order->order_number, [
            'reason' => $request->reason,
            'amount' => $order->total_amount,
        ]);

        return back()->with('success', 'Order marked as paid. Inventory credential assigned.');
    }

    public function resendDelivery(Order $order)
    {
        if (!$order->isPaid()) {
            return back()->with('error', 'Cannot resend delivery for an unpaid order.');
        }

        if (!$order->credential) {
            // Attempt to assign if now available
            $this->inventoryService->allocateCredential($order);
        }

        $this->deliveryService->deliverOrder($order);

        AuditLog::logAction('order_delivery_resent', 'Order', $order->order_number);

        return back()->with('success', 'Delivery notification email dispatched.');
    }

    public function processRefund(Request $request, Order $order)
    {
        $request->validate([
            'refund_reason' => 'required|string|min:5|max:255',
        ]);

        $order->update(['payment_status' => 'refunded']);

        if ($order->payment) {
            $order->payment->update(['status' => 'refunded']);
        }

        AuditLog::logAction('order_refunded', 'Order', $order->order_number, [
            'reason' => $request->refund_reason,
            'amount' => $order->total_amount,
        ]);

        return back()->with('success', 'Order marked as refunded.');
    }

    public function addNote(Request $request, Order $order)
    {
        $request->validate(['note' => 'required|string|max:1000']);

        $currentNotes = $order->notes ? $order->notes . "\n" : '';
        $newNote = '[' . now()->format('Y-m-d H:i') . ' Admin ' . auth()->user()->name . ']: ' . trim($request->note);

        $order->update(['notes' => $currentNotes . $newNote]);

        AuditLog::logAction('order_note_added', 'Order', $order->order_number, [
            'note' => $request->note,
        ]);

        return back()->with('success', 'Note added to order.');
    }

    public function updateFulfillment(Request $request, Order $order)
    {
        $validated = $request->validate([
            'fulfillment_status' => 'required|string|in:pending,processing,delivered,failed,manual_review',
        ]);

        $order->update([
            'fulfillment_status' => $validated['fulfillment_status'],
            'delivery_status' => $validated['fulfillment_status'] === 'delivered' ? 'delivered' : 'pending',
            'delivered_at' => $validated['fulfillment_status'] === 'delivered' ? ($order->delivered_at ?: now()) : null,
        ]);

        AuditLog::logAction('order_fulfillment_updated', 'Order', $order->order_number, [
            'status' => $validated['fulfillment_status'],
        ]);

        return back()->with('success', 'Fulfillment status updated.');
    }

    public function updateCanvaStatus(Request $request, Order $order)
    {
        $validated = $request->validate([
            'canva_bonus_status' => 'required|string|in:not_requested,requested,processing,activated,skipped,failed',
            'canva_email' => 'nullable|email:filter|max:150',
        ]);

        $order->update([
            'canva_bonus_status' => $validated['canva_bonus_status'],
            'canva_email' => !empty($validated['canva_email']) ? strtolower(trim($validated['canva_email'])) : $order->canva_email,
        ]);

        AuditLog::logAction('order_canva_status_updated', 'Order', $order->order_number, [
            'status' => $validated['canva_bonus_status'],
            'canva_email' => $order->canva_email,
        ]);

        return back()->with('success', 'Canva Pro bonus status updated.');
    }
}
