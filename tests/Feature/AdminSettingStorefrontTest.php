<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminSettingStorefrontTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_admin_can_update_store_branding_and_home_reflects_it(): void
    {
        $admin = User::where('is_admin', true)->first();

        // Admin updates settings
        $response = $this->actingAs($admin)->post(route('admin.settings.update'), [
            'store_name' => 'Apex Pro Digital',
            'store_badge' => 'VIP',
            'store_tagline' => 'Premium Software Licenses',
            'whatsapp_number' => '+919999888877',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Check DB
        $this->assertEquals('Apex Pro Digital', Setting::get('store_name'));
        $this->assertEquals('VIP', Setting::get('store_badge'));

        // Check Storefront Home page
        $homeResponse = $this->get('/');
        $homeResponse->assertStatus(200);
        $homeResponse->assertSee('Apex Pro Digital');
        $homeResponse->assertSee('VIP');
    }

    public function test_admin_can_update_store_logo_via_base64_data_uri(): void
    {
        $admin = User::where('is_admin', true)->first();

        // 1x1 transparent png in base64
        $fakePngBase64 = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';

        $response = $this->actingAs($admin)->post(route('admin.settings.update'), [
            'store_logo_data' => $fakePngBase64,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $logoPath = Setting::get('store_logo');
        $this->assertNotEmpty($logoPath);
        $this->assertStringStartsWith('/uploads/branding/logo_', $logoPath);
        $this->assertFileExists(public_path($logoPath));

        // Cleanup created test file
        if (file_exists(public_path($logoPath))) {
            unlink(public_path($logoPath));
        }
    }
}
