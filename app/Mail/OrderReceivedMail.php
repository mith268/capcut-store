<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderReceivedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Order $order) {}

    public function envelope(): Envelope
    {
        $storeName = \App\Models\Setting::get('store_name', 'CapCut Pro Store');
        return new Envelope(
            subject: "Order Received #{$this->order->order_number} - {$storeName}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.order_received',
        );
    }
}
