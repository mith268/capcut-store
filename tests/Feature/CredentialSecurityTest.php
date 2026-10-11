<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\InventoryCredential;
use App\Models\Order;
use App\Models\Product;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CredentialSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_raw_passwords_are_encrypted_in_database_table(): void
    {
        $rawRow = DB::table('inventory_credentials')->first();

        $this->assertNotEmpty($rawRow->password_encrypted);
        // Assert stored string does NOT match the plaintext password
        $this->assertNotEquals('CapCutTeam#2026!a', $rawRow->password_encrypted);
        // Laravel Crypt payloads contain json base64 envelope with iv and mac
        $this->assertTrue(strlen($rawRow->password_encrypted) > 40);
    }

    public function test_unpaid_order_cannot_reveal_credentials(): void
    {
        $product = Product::first();
        $order = Order::create([
            'customer_name' => 'Unpaid User',
            'customer_email' => 'unpaid@example.com',
            'product_id' => $product->id,
            'quantity' => 1,
            'unit_price' => $product->effective_price,
            'total_amount' => $product->effective_price,
            'payment_status' => 'pending', // UNPAID
        ]);

        $response = $this->postJson("/order/{$order->uuid}/{$order->access_token}/reveal");

        $response->assertStatus(403);
        $response->assertJson(['success' => false]);
    }

    public function test_paid_order_with_valid_token_reveals_credentials_and_logs_audit(): void
    {
        $product = Product::first();
        $order = Order::create([
            'customer_name' => 'Authorized User',
            'customer_email' => 'auth@example.com',
            'product_id' => $product->id,
            'quantity' => 1,
            'unit_price' => $product->effective_price,
            'total_amount' => $product->effective_price,
            'payment_status' => 'paid',
        ]);

        $cred = InventoryCredential::where('product_id', $product->id)->where('status', 'available')->first();
        $cred->update(['status' => 'sold', 'order_id' => $order->id, 'sold_at' => now()]);

        $response = $this->postJson("/order/{$order->uuid}/{$order->access_token}/reveal");

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'email' => $cred->email,
            'password' => $cred->password,
        ]);

        // Assert audit log exists and does NOT contain the secret password
        $log = AuditLog::where('action', 'customer_credential_revealed')->latest()->first();
        $this->assertNotNull($log);
        $this->assertStringNotContainsString($cred->password, (string) $log->details);
    }
}
