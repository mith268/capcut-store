<?php

namespace App\Services\WhatsApp;

use App\Models\Product;
use App\Models\Setting;

class WhatsAppService
{
    public function getCleanNumber(): string
    {
        $rawNumber = Setting::get('whatsapp_number') ?: config('services.whatsapp.number', '919876543210');
        return preg_replace('/[^0-9]/', '', $rawNumber);
    }

    public function generateCheckoutUrl(Product $product, ?string $customerName = null, ?string $customerEmail = null): string
    {
        $phone = $this->getCleanNumber();
        $customMessage = Setting::get('whatsapp_message');

        if (!empty($customMessage)) {
            $message = $customMessage;
        } else {
            $message = "Hi, I'm interested in the CapCut Pro 1 Month + 1200 AI Credits offer.";
        }

        return "https://wa.me/{$phone}?text=" . rawurlencode($message);
    }

    public function generateSupportUrl(?string $orderNumber = null): string
    {
        $phone = $this->getCleanNumber();

        if ($orderNumber) {
            $message = "Hello Support, I need help with my CapCut Pro order #{$orderNumber}.";
        } else {
            $message = "Hello Support, I have a question regarding CapCut Pro 1 Month + 1200 AI Credits.";
        }

        return "https://wa.me/{$phone}?text=" . rawurlencode($message);
    }
}
