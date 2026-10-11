<?php

namespace Tests\Feature;

use App\Models\InventoryCredential;
use App\Models\Order;
use App\Models\Product;
use App\Services\Inventory\InventoryService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryAllocationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_inventory_allocation_assigns_single_credential_to_paid_order(): void
    {
        $product = Product::first();
        $service = app(InventoryService::class);

        $order = Order::create([
            'customer_name' => 'John Stock',
            'customer_email' => 'john.stock@example.com',
            'product_id' => $product->id,
            'quantity' => 1,
            'unit_price' => $product->effective_price,
            'total_amount' => $product->effective_price,
            'payment_status' => 'paid',
            'delivery_status' => 'pending',
        ]);

        $credential = $service->allocateCredential($order);

        $this->assertNotNull($credential);
        $this->assertEquals('sold', $credential->status);
        $this->assertEquals($order->id, $credential->order_id);

        $order->refresh();
        $this->assertEquals('ready', $order->delivery_status);
    }

    public function test_two_different_orders_never_receive_same_credential(): void
    {
        $product = Product::first();
        $service = app(InventoryService::class);

        $orderA = Order::create([
            'customer_name' => 'Buyer A',
            'customer_email' => 'a@example.com',
            'product_id' => $product->id,
            'quantity' => 1,
            'unit_price' => $product->effective_price,
            'total_amount' => $product->effective_price,
            'payment_status' => 'paid',
            'delivery_status' => 'pending',
        ]);

        $orderB = Order::create([
            'customer_name' => 'Buyer B',
            'customer_email' => 'b@example.com',
            'product_id' => $product->id,
            'quantity' => 1,
            'unit_price' => $product->effective_price,
            'total_amount' => $product->effective_price,
            'payment_status' => 'paid',
            'delivery_status' => 'pending',
        ]);

        $credA = $service->allocateCredential($orderA);
        $credB = $service->allocateCredential($orderB);

        $this->assertNotNull($credA);
        $this->assertNotNull($credB);
        $this->assertNotEquals($credA->id, $credB->id);
    }

    public function test_out_of_stock_marks_delivery_pending(): void
    {
        $product = Product::first();
        // Clear all available credentials
        InventoryCredential::where('product_id', $product->id)->delete();

        $service = app(InventoryService::class);

        $order = Order::create([
            'customer_name' => 'Unlucky Buyer',
            'customer_email' => 'unlucky@example.com',
            'product_id' => $product->id,
            'quantity' => 1,
            'unit_price' => $product->effective_price,
            'total_amount' => $product->effective_price,
            'payment_status' => 'paid',
            'delivery_status' => 'pending',
        ]);

        $cred = $service->allocateCredential($order);

        $this->assertNull($cred);
        $order->refresh();
        $this->assertEquals('pending', $order->delivery_status);
    }
}
