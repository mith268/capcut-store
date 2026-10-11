<?php

namespace App\Services\Delivery;

use App\Mail\ProductDeliveredMail;
use App\Models\Order;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class DeliveryService
{
    public function deliverOrder(Order $order): bool
    {
        if (!$order->credential) {
            Log::info("Order #{$order->order_number} has no credential assigned yet. Delivery pending restock.");
            return false;
        }

        $order->update([
            'delivery_status' => 'delivered',
            'delivered_at' => now(),
        ]);

        // Dispatch email notification to customer
        try {
            if (config('mail.default') && !empty($order->customer_email)) {
                Mail::to($order->customer_email)->queue(new ProductDeliveredMail($order));
            }
        } catch (Exception $e) {
            Log::error("Failed to send delivery email for order #{$order->order_number}: " . $e->getMessage());
        }

        return true;
    }
}
