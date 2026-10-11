<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MobileHeroAndTutorialUiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Product::create([
            'name' => 'CapCut Pro — 1 Month (1200 AI Credits)',
            'slug' => 'capcut-pro-1-month',
            'hero_title' => 'CapCut Pro — 1 Month',
            'hero_subtitle' => '1200 AI Credits Included + Canva Pro 1 Year Bonus',
            'price' => 1999.00,
            'sale_price' => 399.00,
            'duration_days' => 30,
            'ai_credits' => 1200,
            'warranty_days' => 30,
            'features' => ['1200 AI Credits', 'Canva Pro 1 Year', '2 Devices'],
            'is_active' => true,
        ]);

        Setting::set('store_name', 'Convora');
        Setting::set('whatsapp_number', '919876543210');
    }

    public function test_all_whatsapp_ctas_say_whatsapp_and_not_buy_via_whatsapp(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);

        // Assert "BUY VIA WHATSAPP" is NOT present anywhere on the page
        $response->assertDontSee('BUY VIA WHATSAPP');
        $response->assertDontSee('Buy via WhatsApp');

        // Assert "WHATSAPP" CTA is present
        $response->assertSee('WHATSAPP');
    }

    public function test_youtube_video_embed_is_preserved(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('https://www.youtube-nocookie.com/embed/j_PZmYQDJQA?si=vpZnqozaGSsuNix_', false);
    }

    public function test_promotional_and_trust_lines_removed_from_below_video(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        // The old line under the video
        $response->assertDontSee('Watch how CapCut Pro powers high-end video editing workflows');
    }

    public function test_tutorial_steps_to_login_section_is_present_with_required_elements(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Tutorial Steps to Login to CapCut');
        $response->assertSee('Follow these simple steps to connect, sign in, verify your access, and start editing.');
        $response->assertSee('Download &amp; Connect VPN', false);
        $response->assertSee('Download CapCut');
        $response->assertSee('Login With Your Provided Account');
        $response->assertSee('Verify CapCut Pro Access');
        $response->assertSee('Windows &amp; Mac Desktop Login', false);
        $response->assertSee('PHONE LOGIN FIRST');
        $response->assertSee('QR CODE LOGIN');
        $response->assertSee('DESKTOP ACCESS');
        $response->assertSee('Regional Availability Guidance');
        $response->assertSee('Availability can vary by region and device');
        $response->assertSee('Connect your device to your network before launching CapCut');
    }

    public function test_device_cards_have_mobile_centering_classes(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        // The mobile classes centering the card and icons
        $response->assertSee('text-center md:text-left', false);
        $response->assertSee('mx-auto md:mx-0', false);
        $response->assertSee('flex flex-col md:flex-row items-center md:justify-between', false);
    }
}
