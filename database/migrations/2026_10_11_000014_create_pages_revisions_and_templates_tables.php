<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('theme_preset', 50)->default('dark_neon'); // dark_neon, light_clean, luxury_emerald
            $table->string('status', 30)->default('draft')->index(); // published, draft, archived
            $table->boolean('is_homepage')->default(false)->index();
            $table->unsignedBigInteger('published_version_id')->nullable();
            $table->json('draft_sections');
            $table->json('published_sections')->nullable();
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('social_image')->nullable();
            $table->json('custom_css_vars')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });

        Schema::create('page_revisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('page_id')->constrained()->cascadeOnDelete();
            $table->integer('version_number');
            $table->json('sections');
            $table->string('theme_preset', 50)->default('dark_neon');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('change_summary')->nullable();
            $table->timestamps();
        });

        Schema::create('page_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('theme_preset', 50)->default('dark_neon');
            $table->json('sections');
            $table->string('thumbnail')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('page_templates');
        Schema::dropIfExists('page_revisions');
        Schema::dropIfExists('pages');
    }
};
