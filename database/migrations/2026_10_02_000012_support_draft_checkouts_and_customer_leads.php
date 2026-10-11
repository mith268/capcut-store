<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('customer_name')->nullable()->change();
            $table->string('customer_email')->nullable()->change();

            if (!Schema::hasColumn('orders', 'checkout_session_id')) {
                $table->string('checkout_session_id', 100)->nullable()->index()->after('access_token');
            }
            if (!Schema::hasColumn('orders', 'checkout_status')) {
                $table->string('checkout_status', 30)->default('draft')->index()->after('checkout_session_id');
            }
            if (!Schema::hasColumn('orders', 'last_activity_at')) {
                $table->timestamp('last_activity_at')->nullable()->after('updated_at');
            }
            if (!Schema::hasColumn('orders', 'payment_attempted_at')) {
                $table->timestamp('payment_attempted_at')->nullable()->after('last_activity_at');
            }
            if (!Schema::hasColumn('orders', 'abandoned_at')) {
                $table->timestamp('abandoned_at')->nullable()->after('payment_attempted_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'checkout_session_id',
                'checkout_status',
                'last_activity_at',
                'payment_attempted_at',
                'abandoned_at',
            ]);
        });
    }
};
