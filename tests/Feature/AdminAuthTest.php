<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_guest_is_redirected_from_admin_dashboard_to_login(): void
    {
        $response = $this->get('/admin');
        $response->assertRedirect('/admin/login');
    }

    public function test_non_admin_user_is_denied_access_to_admin(): void
    {
        $regularUser = User::create([
            'name' => 'Regular Customer',
            'email' => 'customer@example.com',
            'password' => bcrypt('Password123!'),
            'is_admin' => false,
        ]);

        $response = $this->actingAs($regularUser)->get('/admin');
        $response->assertRedirect('/admin/login');
    }

    public function test_admin_can_login_with_valid_credentials(): void
    {
        $response = $this->post('/admin/login', [
            'email' => 'admin@capcutstore.local',
            'password' => 'Admin@CapCut2026!',
        ]);

        $response->assertRedirect('/admin');
        $this->assertAuthenticated();
    }

    public function test_failed_login_is_throttled_after_consecutive_failures(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->post('/admin/login', [
                'email' => 'admin@capcutstore.local',
                'password' => 'WrongPassword!',
            ]);
        }

        // 6th attempt should be throttled
        $response = $this->post('/admin/login', [
            'email' => 'admin@capcutstore.local',
            'password' => 'WrongPassword!',
        ]);

        $response->assertSessionHas('error');
        $this->assertStringContainsString('Too many failed attempts', session('error'));
    }
}
