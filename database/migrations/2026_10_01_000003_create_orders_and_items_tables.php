<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number', 50)->unique();
            $table->uuid('uuid')->unique();
            $table->string('access_token', 64)->unique();
            $table->string('customer_name');
            $table->string('customer_email')->index();
            $table->string('customer_phone', 50)->nullable();
            $table->string('customer_country', 10)->default('IN');
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 10, 2);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->foreignId('coupon_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('total_amount', 10, 2);
            $table->string('currency', 10)->default('INR');
            $table->enum('payment_status', [
                'pending',
                'payment_initiated',
                'paid',
                'processing',
                'delivered',
                'cancelled',
                'refunded',
                'failed'
            ])->default('pending')->index();
            $table->enum('delivery_status', [
                'pending',
                'assigned',
                'ready',
                'delivered',
                'failed'
            ])->default('pending')->index();
            $table->text('notes')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('credentials_revealed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('product_name');
            $table->decimal('unit_price', 10, 2);
            $table->integer('quantity')->default(1);
            $table->decimal('subtotal', 10, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
