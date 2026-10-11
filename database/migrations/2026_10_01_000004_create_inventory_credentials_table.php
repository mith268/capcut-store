<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_credentials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->text('email_encrypted');
            $table->text('password_encrypted');
            $table->text('notes_encrypted')->nullable();
            $table->enum('status', ['available', 'reserved', 'sold', 'disabled'])->default('available')->index();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('reserved_at')->nullable();
            $table->timestamp('sold_at')->nullable();
            $table->timestamps();

            $table->index(['product_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_credentials');
    }
};
