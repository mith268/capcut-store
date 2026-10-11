<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\Setting;
use App\Models\TrackingEvent;
use App\Models\User;
use App\Services\Payment\RazorpayPaymentService;
use App\Services\Tracking\TrackingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AdminRazorpayControlAndSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();

        $this->adminUser = User::where('is_admin', true)->first() ?? User::factory()->create([
            'is_admin' => true,
            'password' => bcrypt('password123'),
        ]);
    }

    public function test_admin_can_save_razorpay_credentials_encrypted_in_database(): void
    {
        $this->actingAs($this->adminUser);

        $testKeyId = 'rzp_test_AdminConfigured123';
        $testSecret = 'AdminSecret987654321';

        $response = $this->post(route('admin.settings.update'), [
            'store_name' => 'CapCut Pro Dynamic Store',
            'razorpay_mode' => 'test',
            'razorpay_key_id' => $testKeyId,
            'razorpay_key_secret' => $testSecret,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Verify in database: key_id is saved, key_secret is encrypted
        $this->assertEquals($testKeyId, Setting::get('razorpay_key_id'));
        $this->assertEquals($testSecret, Setting::get('razorpay_key_secret'));
        $this->assertEquals('test', Setting::get('razorpay_mode'));

        // Verify raw database record is actually encrypted and does not contain plain secret
        $rawRecord = Setting::where('key', 'razorpay_key_secret')->first();
        $this->assertTrue($rawRecord->is_encrypted);
        $this->assertNotEquals($testSecret, $rawRecord->value);
        $this->assertEquals($testSecret, Crypt::decryptString($rawRecord->value));
    }

    public function test_razorpay_secret_is_never_rendered_in_settings_blade_html(): void
    {
        $this->actingAs($this->adminUser);

        $plainSecret = 'SUPER_CONFIDENTIAL_SECRET_XYZ999';
        Setting::set('razorpay_key_secret', $plainSecret, true, 'payment');
        Setting::set('razorpay_key_id', 'rzp_test_SecretCheck123', false, 'payment');

        $response = $this->get(route('admin.settings.index'));

        $response->assertStatus(200);
        $response->assertDontSee($plainSecret);
        $response->assertSee('Encrypted in database');
    }

    public function test_razorpay_service_uses_admin_credentials_as_source_of_truth(): void
    {
        $service = app(RazorpayPaymentService::class);

        Setting::set('razorpay_key_id', 'rzp_test_DbSourceKey', false, 'payment');
        Setting::set('razorpay_key_secret', 'DbSourceSecretValue', true, 'payment');
        Setting::set('razorpay_mode', 'test', false, 'payment');

        $this->assertEquals('rzp_test_DbSourceKey', $service->getKeyId());
        $this->assertEquals('DbSourceSecretValue', $service->getKeySecret());
        $this->assertEquals('test', $service->getMode());
        $this->assertFalse($service->isLiveMode());
    }

    public function test_admin_test_connection_endpoint_validates_successfully_via_mock(): void
    {
        $this->actingAs($this->adminUser);

        Setting::set('razorpay_key_id', 'rzp_test_ConnectionCheckKey', false, 'payment');
        Setting::set('razorpay_key_secret', 'ConnectionCheckSecret', true, 'payment');
        Setting::set('razorpay_mode', 'test', false, 'payment');

        Http::fake([
            'https://api.razorpay.com/v1/orders*' => Http::response([
                'entity' => 'collection',
                'count' => 0,
                'items' => [],
            ], 200),
        ]);

        $response = $this->postJson(route('admin.settings.test_razorpay'), [
            'razorpay_mode' => 'test',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'mode' => 'test',
        ]);
        $response->assertSee('Connected Successfully');
    }

    public function test_admin_test_connection_handles_invalid_credentials_gracefully(): void
    {
        $this->actingAs($this->adminUser);

        Setting::set('razorpay_key_id', 'rzp_test_InvalidKey', false, 'payment');
        Setting::set('razorpay_key_secret', 'InvalidSecret', true, 'payment');
        Setting::set('razorpay_mode', 'test', false, 'payment');

        Http::fake([
            'https://api.razorpay.com/v1/orders*' => Http::response([
                'error' => [
                    'code' => 'BAD_REQUEST_ERROR',
                    'description' => 'The id provided does not exist',
                ],
            ], 401),
        ]);

        $response = $this->postJson(route('admin.settings.test_razorpay'), [
            'razorpay_mode' => 'test',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => false,
            'mode' => 'test',
        ]);
        $response->assertSee('Invalid credentials');
    }

    public function test_unauthenticated_user_cannot_call_test_connection_endpoint(): void
    {
        $response = $this->postJson(route('admin.settings.test_razorpay'), [
            'razorpay_mode' => 'test',
        ]);

        $response->assertStatus(401);
    }

    public function test_tracking_service_deduplicates_by_event_id_and_strips_sensitive_data(): void
    {
        $trackingService = app(TrackingService::class);

        $uniqueEventId = 'evt_test_dedup_' . bin2hex(random_bytes(6));

        // First event insertion
        $event1 = $trackingService->trackEvent(
            'Checkout Started',
            $uniqueEventId,
            null,
            [
                'step' => 'contact_info',
                'customer_password' => 'secret123',
                'key_secret' => 'confidential_key',
                'safe_param' => 'allowed_value',
            ]
        );

        $this->assertEquals('Checkout Started', $event1->event_name);
        $this->assertEquals($uniqueEventId, $event1->event_id);
        $this->assertArrayHasKey('safe_param', $event1->payload);
        $this->assertArrayNotHasKey('customer_password', $event1->payload);
        $this->assertArrayNotHasKey('key_secret', $event1->payload);

        // Second attempt with exact same event_id (e.g. page refresh)
        $event2 = $trackingService->trackEvent(
            'Checkout Started',
            $uniqueEventId,
            null,
            ['step' => 'duplicate_attempt']
        );

        $this->assertEquals($event1->id, $event2->id);
        $this->assertEquals(1, TrackingEvent::where('event_id', $uniqueEventId)->count());
    }
}
