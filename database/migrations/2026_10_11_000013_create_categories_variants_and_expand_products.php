<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Create categories table
        if (!Schema::hasTable('categories')) {
            Schema::create('categories', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->string('icon', 50)->default('sparkles');
                $table->string('image')->nullable();
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(true)->index();
                $table->integer('display_order')->default(0);
                $table->timestamps();
            });
        }

        // 2. Add columns to products table
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'category_id')) {
                $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            }
            if (!Schema::hasColumn('products', 'sku')) {
                $table->string('sku', 100)->nullable()->index();
            }
            if (!Schema::hasColumn('products', 'primary_image')) {
                $table->string('primary_image')->nullable();
            }
            if (!Schema::hasColumn('products', 'tags')) {
                $table->json('tags')->nullable();
            }
            if (!Schema::hasColumn('products', 'status')) {
                $table->string('status', 30)->default('active')->index();
            }
            if (!Schema::hasColumn('products', 'show_in_store')) {
                $table->boolean('show_in_store')->default(true)->index();
            }
            if (!Schema::hasColumn('products', 'visibility')) {
                $table->string('visibility', 30)->default('public'); // public, unlisted
            }
            if (!Schema::hasColumn('products', 'purchase_mode')) {
                $table->string('purchase_mode', 30)->default('both'); // checkout, whatsapp, both
            }
            if (!Schema::hasColumn('products', 'fulfillment_mode')) {
                $table->string('fulfillment_mode', 50)->default('key_inventory'); // manual, key_inventory, file_download, account_access
            }
            if (!Schema::hasColumn('products', 'inventory_type')) {
                $table->string('inventory_type', 30)->default('finite'); // finite, unlimited
            }
            if (!Schema::hasColumn('products', 'low_stock_threshold')) {
                $table->integer('low_stock_threshold')->default(5);
            }
            if (!Schema::hasColumn('products', 'max_order_quantity')) {
                $table->integer('max_order_quantity')->default(5);
            }
            if (!Schema::hasColumn('products', 'video_provider')) {
                $table->string('video_provider', 30)->default('youtube');
            }
            if (!Schema::hasColumn('products', 'video_id')) {
                $table->string('video_id', 100)->nullable();
            }
            if (!Schema::hasColumn('products', 'video_poster')) {
                $table->string('video_poster')->nullable();
            }
            if (!Schema::hasColumn('products', 'badge_text')) {
                $table->string('badge_text', 100)->nullable();
            }
            if (!Schema::hasColumn('products', 'whatsapp_message')) {
                $table->text('whatsapp_message')->nullable();
            }
            if (!Schema::hasColumn('products', 'activation_instructions')) {
                $table->text('activation_instructions')->nullable();
            }
            if (!Schema::hasColumn('products', 'exclusions')) {
                $table->text('exclusions')->nullable();
            }
            if (!Schema::hasColumn('products', 'warranty_terms')) {
                $table->text('warranty_terms')->nullable();
            }
            if (!Schema::hasColumn('products', 'meta_title')) {
                $table->string('meta_title')->nullable();
            }
            if (!Schema::hasColumn('products', 'meta_description')) {
                $table->text('meta_description')->nullable();
            }
            if (!Schema::hasColumn('products', 'social_image')) {
                $table->string('social_image')->nullable();
            }
            if (!Schema::hasColumn('products', 'bonus_title')) {
                $table->string('bonus_title')->nullable();
            }
            if (!Schema::hasColumn('products', 'bonus_description')) {
                $table->text('bonus_description')->nullable();
            }
            if (!Schema::hasColumn('products', 'display_order')) {
                $table->integer('display_order')->default(0);
            }
        });

        // 3. Create product variants table
        if (!Schema::hasTable('product_variants')) {
            Schema::create('product_variants', function (Blueprint $table) {
                $table->id();
                $table->foreignId('product_id')->constrained()->cascadeOnDelete();
                $table->string('name');
                $table->string('sku', 100)->nullable();
                $table->decimal('price', 10, 2);
                $table->decimal('sale_price', 10, 2)->nullable();
                $table->string('duration', 50)->nullable();
                $table->integer('device_limit')->nullable();
                $table->integer('credits')->nullable();
                $table->integer('warranty_days')->nullable();
                $table->string('fulfillment_mode', 50)->nullable();
                $table->boolean('is_default')->default(false);
                $table->boolean('is_active')->default(true);
                $table->integer('display_order')->default(0);
                $table->timestamps();
            });
        }

        // 4. Create product media table
        if (!Schema::hasTable('product_media')) {
            Schema::create('product_media', function (Blueprint $table) {
                $table->id();
                $table->foreignId('product_id')->constrained()->cascadeOnDelete();
                $table->string('file_path');
                $table->string('file_name')->nullable();
                $table->string('mime_type', 100)->nullable();
                $table->unsignedBigInteger('file_size')->nullable();
                $table->string('alt_text')->nullable();
                $table->boolean('is_primary')->default(false);
                $table->integer('display_order')->default(0);
                $table->timestamps();
            });
        }

        // 5. Create product files table (private downloads)
        if (!Schema::hasTable('product_files')) {
            Schema::create('product_files', function (Blueprint $table) {
                $table->id();
                $table->foreignId('product_id')->constrained()->cascadeOnDelete();
                $table->foreignId('variant_id')->nullable()->constrained('product_variants')->nullOnDelete();
                $table->string('file_path');
                $table->string('file_name');
                $table->unsignedBigInteger('file_size');
                $table->string('mime_type', 100)->nullable();
                $table->integer('max_downloads')->default(5);
                $table->timestamps();
            });
        }

        // 6. Update order_items and inventory_credentials for variant reference
        Schema::table('order_items', function (Blueprint $table) {
            if (!Schema::hasColumn('order_items', 'variant_id')) {
                $table->foreignId('variant_id')->nullable()->after('product_id')->constrained('product_variants')->nullOnDelete();
            }
            if (!Schema::hasColumn('order_items', 'variant_name')) {
                $table->string('variant_name')->nullable()->after('product_name');
            }
            if (!Schema::hasColumn('order_items', 'fulfillment_mode')) {
                $table->string('fulfillment_mode', 50)->default('key_inventory')->after('subtotal');
            }
        });

        Schema::table('inventory_credentials', function (Blueprint $table) {
            if (!Schema::hasColumn('inventory_credentials', 'variant_id')) {
                $table->foreignId('variant_id')->nullable()->after('product_id')->constrained('product_variants')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('inventory_credentials', function (Blueprint $table) {
            if (Schema::hasColumn('inventory_credentials', 'variant_id')) {
                $table->dropForeign(['variant_id']);
                $table->dropColumn('variant_id');
            }
        });

        Schema::table('order_items', function (Blueprint $table) {
            if (Schema::hasColumn('order_items', 'variant_id')) {
                $table->dropForeign(['variant_id']);
                $table->dropColumn(['variant_id', 'variant_name', 'fulfillment_mode']);
            }
        });

        Schema::dropIfExists('product_files');
        Schema::dropIfExists('product_media');
        Schema::dropIfExists('product_variants');

        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'category_id')) {
                $table->dropForeign(['category_id']);
                $table->dropColumn([
                    'category_id', 'sku', 'primary_image', 'tags', 'status',
                    'show_in_store', 'visibility', 'purchase_mode', 'fulfillment_mode',
                    'inventory_type', 'low_stock_threshold', 'max_order_quantity',
                    'video_provider', 'video_id', 'video_poster', 'badge_text',
                    'whatsapp_message', 'activation_instructions', 'exclusions',
                    'warranty_terms', 'meta_title', 'meta_description', 'social_image',
                    'bonus_title', 'bonus_description', 'display_order'
                ]);
            }
        });

        Schema::dropIfExists('categories');
    }
};
