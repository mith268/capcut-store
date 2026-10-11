<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'canva_email')) {
                $table->string('canva_email')->nullable()->after('customer_country');
            }
            if (!Schema::hasColumn('orders', 'canva_bonus_status')) {
                $table->string('canva_bonus_status', 30)->default('not_requested')->index()->after('canva_email');
            }
            if (!Schema::hasColumn('orders', 'payment_method')) {
                $table->string('payment_method', 30)->default('razorpay')->nullable()->after('currency');
            }
            if (!Schema::hasColumn('orders', 'gateway')) {
                $table->string('gateway', 30)->default('razorpay')->nullable()->after('payment_method');
            }
            if (!Schema::hasColumn('orders', 'gateway_order_id')) {
                $table->string('gateway_order_id', 100)->nullable()->index()->after('gateway');
            }
            if (!Schema::hasColumn('orders', 'gateway_payment_id')) {
                $table->string('gateway_payment_id', 100)->nullable()->index()->after('gateway_order_id');
            }
            if (!Schema::hasColumn('orders', 'fulfillment_status')) {
                $table->string('fulfillment_status', 30)->default('pending')->index()->after('delivery_status');
            }
            if (!Schema::hasColumn('orders', 'refund_status')) {
                $table->string('refund_status', 30)->default('none')->index()->after('fulfillment_status');
            }
            if (!Schema::hasColumn('orders', 'meta_data')) {
                $table->json('meta_data')->nullable()->after('notes');
            }
            if (!Schema::hasColumn('orders', 'utm_source')) {
                $table->string('utm_source', 100)->nullable()->after('meta_data');
                $table->string('utm_medium', 100)->nullable()->after('utm_source');
                $table->string('utm_campaign', 100)->nullable()->after('utm_medium');
                $table->string('utm_content', 100)->nullable()->after('utm_campaign');
                $table->string('utm_term', 100)->nullable()->after('utm_content');
                $table->string('fbp', 100)->nullable()->after('utm_term');
                $table->string('fbc', 100)->nullable()->after('fbp');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'canva_email',
                'canva_bonus_status',
                'payment_method',
                'gateway',
                'gateway_order_id',
                'gateway_payment_id',
                'fulfillment_status',
                'refund_status',
                'meta_data',
                'utm_source',
                'utm_medium',
                'utm_campaign',
                'utm_content',
                'utm_term',
                'fbp',
                'fbc',
            ]);
        });
    }
};
