<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ProductDeliveredMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Order $order) {}

    public function envelope(): Envelope
    {
        $productTitle = $this->order->product ? $this->order->product->name : \App\Models\Setting::get('store_name', 'CapCut Pro');
        return new Envelope(
            subject: "🎉 Your {$productTitle} Access is Ready! Order #{$this->order->order_number}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.product_delivered',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
