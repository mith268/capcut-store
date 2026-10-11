<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('warranty_policies', function (Blueprint $table) {
            $table->id();
            $table->string('title')->default('30-Day Digital Warranty');
            $table->integer('warranty_days')->default(30);
            $table->longText('policy_content');
            $table->text('eligible_issues')->nullable();
            $table->text('excluded_issues')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('refund_policies', function (Blueprint $table) {
            $table->id();
            $table->string('title')->default('Digital Refund & Replacement Policy');
            $table->longText('refund_conditions');
            $table->longText('replacement_conditions')->nullable();
            $table->longText('excluded_conditions')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('refund_policies');
        Schema::dropIfExists('warranty_policies');
    }
};
