<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InventoryCredential;
use App\Models\Order;
use App\Models\TrackingEvent;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $today = Carbon::today();
        $thisMonth = Carbon::now()->startOfMonth();

        // Financial Metrics
        $todaySales = Order::whereIn('payment_status', ['paid', 'delivered'])
            ->whereDate('created_at', $today)
            ->sum('total_amount');

        $monthSales = Order::whereIn('payment_status', ['paid', 'delivered'])
            ->where('created_at', '>=', $thisMonth)
            ->sum('total_amount');

        $totalSales = Order::whereIn('payment_status', ['paid', 'delivered'])
            ->sum('total_amount');

        // Order Counts
        $totalOrders = Order::count();
        $paidOrders = Order::whereIn('payment_status', ['paid', 'delivered'])->count();
        $pendingOrders = Order::where('payment_status', 'pending')->count();
        $refundedOrders = Order::where('payment_status', 'refunded')->count();

        // Inventory Metrics
        $availableInventory = InventoryCredential::where('status', 'available')->count();
        $soldInventory = InventoryCredential::where('status', 'sold')->count();

        // Tracking & Conversion Metrics
        $whatsappClicks = TrackingEvent::where('event_name', 'WhatsAppCheckoutClick')->count();
        $checkoutStarts = TrackingEvent::where('event_name', 'InitiateCheckout')->count();
        $pageViews = TrackingEvent::where('event_name', 'PageView')->count();

        $conversionRate = $checkoutStarts > 0 ? round(($paidOrders / $checkoutStarts) * 100, 1) : 0.0;

        // Daily Revenue Chart Data (Last 14 Days)
        $chartDates = [];
        $chartRevenue = [];
        $chartOrders = [];

        for ($i = 13; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $dateStr = $date->format('Y-m-d');
            $label = $date->format('M d');

            $chartDates[] = $label;

            $rev = Order::whereIn('payment_status', ['paid', 'delivered'])
                ->whereDate('created_at', $dateStr)
                ->sum('total_amount');
            $chartRevenue[] = (float) $rev;

            $cnt = Order::whereIn('payment_status', ['paid', 'delivered'])
                ->whereDate('created_at', $dateStr)
                ->count();
            $chartOrders[] = $cnt;
        }

        // Recent Orders
        $recentOrders = Order::with('product')
            ->latest()
            ->take(10)
            ->get();

        return view('admin.dashboard.index', compact(
            'todaySales',
            'monthSales',
            'totalSales',
            'totalOrders',
            'paidOrders',
            'pendingOrders',
            'refundedOrders',
            'availableInventory',
            'soldInventory',
            'whatsappClicks',
            'checkoutStarts',
            'pageViews',
            'conversionRate',
            'chartDates',
            'chartRevenue',
            'chartOrders',
            'recentOrders'
        ));
    }
}
