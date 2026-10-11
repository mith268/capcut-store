<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // General Store Info
            ['key' => 'store_name', 'value' => 'CapCut Pro', 'group' => 'general', 'encrypted' => false],
            ['key' => 'store_badge', 'value' => 'PRO', 'group' => 'general', 'encrypted' => false],
            ['key' => 'store_logo', 'value' => '', 'group' => 'general', 'encrypted' => false],
            ['key' => 'store_tagline', 'value' => 'Digital License Store', 'group' => 'general', 'encrypted' => false],
            ['key' => 'support_email', 'value' => 'support@capcutstore.local', 'group' => 'general', 'encrypted' => false],
            ['key' => 'whatsapp_number', 'value' => env('WHATSAPP_NUMBER', '919876543210'), 'group' => 'general', 'encrypted' => false],
            ['key' => 'whatsapp_cta_text', 'value' => '💬 Buy via WhatsApp', 'group' => 'general', 'encrypted' => false],
            ['key' => 'whatsapp_message', 'value' => 'Hi, I\'m interested in the CapCut Pro 1 Month + 1200 AI Credits offer.', 'group' => 'general', 'encrypted' => false],
            ['key' => 'currency', 'value' => 'INR', 'group' => 'general', 'encrypted' => false],
            ['key' => 'currency_symbol', 'value' => '₹', 'group' => 'general', 'encrypted' => false],
            
            // Hero Configuration
            ['key' => 'hero_eyebrow', 'value' => '🔥 LIMITED-TIME DIGITAL OFFER', 'group' => 'hero', 'encrypted' => false],
            ['key' => 'hero_headline', 'value' => 'CapCut Pro — 1 Month', 'group' => 'hero', 'encrypted' => false],
            ['key' => 'hero_subheadline', 'value' => '1200 AI Credits + Canva Pro 1 Year Bonus', 'group' => 'hero', 'encrypted' => false],
            ['key' => 'hero_supporting', 'value' => 'Premium video editing access with 1200 AI Credits, plus a Canva Pro bonus. Supports mobile, Windows and Mac, with access for up to 2 devices.', 'group' => 'hero', 'encrypted' => false],
            ['key' => 'hero_cta_text', 'value' => '⚡ Get CapCut Pro Now', 'group' => 'hero', 'encrypted' => false],
            ['key' => 'hero_secondary_cta_text', 'value' => '💬 Buy via WhatsApp', 'group' => 'hero', 'encrypted' => false],
            ['key' => 'trust_text', 'value' => 'Secure Payment • Fast Digital Delivery • 30-Day Warranty', 'group' => 'hero', 'encrypted' => false],
            ['key' => 'youtube_video_id', 'value' => 'j_PZmYQDJQA', 'group' => 'hero', 'encrypted' => false],
            ['key' => 'youtube_embed_url', 'value' => 'https://www.youtube.com/embed/j_PZmYQDJQA', 'group' => 'hero', 'encrypted' => false],

            // Canva Bonus Configuration
            ['key' => 'canva_bonus_title', 'value' => '🎁 BONUS: CANVA PRO — 1 YEAR', 'group' => 'canva', 'encrypted' => false],
            ['key' => 'canva_bonus_subtitle', 'value' => 'Complete your CapCut Pro purchase and claim your Canva Pro 1-year bonus.', 'group' => 'canva', 'encrypted' => false],
            ['key' => 'canva_bonus_explanation', 'value' => 'Canva Pro activation requires an eligible email address. You can provide your email after successful payment.', 'group' => 'canva', 'encrypted' => false],

            // Announcement Bar
            ['key' => 'announcement_bar_enabled', 'value' => '1', 'group' => 'announcement', 'encrypted' => false],
            ['key' => 'announcement_bar_text', 'value' => '🔥 LIMITED-TIME DIGITAL OFFER • 1200 AI CREDITS + CANVA PRO 1 YEAR FREE • 30-DAY WARRANTY', 'group' => 'announcement', 'encrypted' => false],
            
            // Branding & Legal Disclaimer
            ['key' => 'legal_disclaimer', 'value' => 'CapCut is a trademark of its respective owner. This store is independent unless otherwise stated. Canva is a trademark of Canva Pty Ltd. The Canva bonus is provided according to the stated offer terms.', 'group' => 'legal', 'encrypted' => false],
            ['key' => 'device_terms_notice', 'value' => 'Login is strictly limited to 1 mobile device + 1 laptop/desktop device. Avoid excessive device switching or repeated login attempts. Exceeding stated limits voids warranty coverage.', 'group' => 'legal', 'encrypted' => false],
            
            // Razorpay Credentials
            ['key' => 'razorpay_key_id', 'value' => env('RAZORPAY_KEY_ID', 'rzp_test_placeholder'), 'group' => 'payment', 'encrypted' => false],
            ['key' => 'razorpay_key_secret', 'value' => env('RAZORPAY_KEY_SECRET', 'test_secret_placeholder'), 'group' => 'payment', 'encrypted' => true],
            ['key' => 'razorpay_webhook_secret', 'value' => env('RAZORPAY_WEBHOOK_SECRET', 'webhook_secret_placeholder'), 'group' => 'payment', 'encrypted' => true],
            
            // Meta Tracking
            ['key' => 'meta_pixel_id', 'value' => env('META_PIXEL_ID', ''), 'group' => 'tracking', 'encrypted' => false],
            ['key' => 'meta_access_token', 'value' => env('META_ACCESS_TOKEN', ''), 'group' => 'tracking', 'encrypted' => true],
            ['key' => 'meta_test_event_code', 'value' => env('META_TEST_EVENT_CODE', ''), 'group' => 'tracking', 'encrypted' => false],
            
            // Trust & Conversion
            ['key' => 'orders_completed_count', 'value' => '0', 'group' => 'conversion', 'encrypted' => false],
            ['key' => 'show_completed_orders', 'value' => '0', 'group' => 'conversion', 'encrypted' => false],
            ['key' => 'default_warranty_days', 'value' => '30', 'group' => 'conversion', 'encrypted' => false],
            ['key' => 'delivery_time_text', 'value' => 'Fast Digital Delivery', 'group' => 'conversion', 'encrypted' => false],
        ];

        foreach ($settings as $item) {
            Setting::set($item['key'], $item['value'], $item['encrypted'], $item['group']);
        }
    }
}
