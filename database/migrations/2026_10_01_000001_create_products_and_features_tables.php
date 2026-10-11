<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->text('short_description')->nullable();
            $table->decimal('price', 10, 2);
            $table->decimal('sale_price', 10, 2)->nullable();
            $table->string('currency', 10)->default('INR');
            $table->string('duration', 50)->default('1 Month');
            $table->integer('credits')->default(1200);
            $table->integer('warranty_days')->default(25);
            $table->string('stock_mode', 50)->default('digital_credentials');
            $table->boolean('active')->default(true)->index();
            $table->boolean('featured')->default(true);
            $table->string('hero_title')->nullable();
            $table->text('hero_subtitle')->nullable();
            $table->string('cta_text')->default('GET CAPCUT PRO NOW');
            $table->timestamps();
        });

        Schema::create('product_features', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('icon', 50)->default('sparkles');
            $table->integer('display_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_features');
        Schema::dropIfExists('products');
    }
};
