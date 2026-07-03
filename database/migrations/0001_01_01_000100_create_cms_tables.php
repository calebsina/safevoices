<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * CMS - dynamic frontend & landing page (Appendix 2, section 13).
 * Public CMS media lives on a separate PUBLIC disk from the evidence
 * vault - never mix reporter evidence into CMS media.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 150)->unique();
            $table->string('key', 64)->nullable()->unique(); // stable key for system pages
            $table->string('template', 64)->default('default');
            $table->string('status', 10)->default('draft');  // draft / published
            $table->foreignId('parent_id')->nullable()->constrained('pages')->nullOnDelete();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_system')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('page_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('page_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 5);
            $table->string('title', 200);
            $table->string('localized_slug', 150)->nullable();
            $table->longText('body')->nullable();
            $table->string('meta_title', 200)->nullable();
            $table->string('meta_description', 300)->nullable();
            $table->unique(['page_id', 'locale']);
            $table->foreign('locale')->references('code')->on('locales');
        });

        Schema::create('content_blocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('page_id')->nullable()->constrained()->cascadeOnDelete(); // NULL = global block
            $table->string('key', 64);                       // hero, steps, cta, stats
            $table->string('type', 40);
            $table->jsonb('settings')->nullable();           // non-text config
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('content_block_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('content_block_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 5);
            $table->string('heading')->nullable();
            $table->string('subheading')->nullable();
            $table->text('body')->nullable();
            $table->string('cta_label', 100)->nullable();
            $table->string('cta_url')->nullable();
            $table->jsonb('extra')->nullable();              // type-specific translatable items
            $table->unique(['content_block_id', 'locale']);
            $table->foreign('locale')->references('code')->on('locales');
        });

        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->string('key', 64)->unique();             // header / footer
            $table->boolean('is_active')->default(true);
        });

        Schema::create('menu_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_id')->constrained()->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('menu_items')->cascadeOnDelete();
            $table->foreignId('page_id')->nullable()->constrained('pages')->nullOnDelete();
            $table->string('url')->nullable();               // external link
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
        });

        Schema::create('menu_item_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_item_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 5);
            $table->string('label', 150);
            $table->unique(['menu_item_id', 'locale']);
            $table->foreign('locale')->references('code')->on('locales');
        });

        Schema::create('faqs', function (Blueprint $table) {
            $table->id();
            $table->string('category', 64)->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('faq_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('faq_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 5);
            $table->string('question', 500);
            $table->text('answer');
            $table->unique(['faq_id', 'locale']);
            $table->foreign('locale')->references('code')->on('locales');
        });

        Schema::create('media_assets', function (Blueprint $table) {
            $table->id();
            $table->string('disk', 50)->default('public');
            $table->string('path');
            $table->string('filename');
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('size');
            $table->foreignUuid('created_by')->nullable()->constrained('users');
            $table->timestamps();
        });

        Schema::create('media_asset_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('media_asset_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 5);
            $table->string('alt_text')->nullable();
            $table->string('caption', 500)->nullable();
            $table->unique(['media_asset_id', 'locale']);
            $table->foreign('locale')->references('code')->on('locales');
        });
    }

    public function down(): void
    {
        $tables = [
            'media_asset_translations', 'media_assets',
            'faq_translations', 'faqs',
            'menu_item_translations', 'menu_items', 'menus',
            'content_block_translations', 'content_blocks',
            'page_translations', 'pages',
        ];

        foreach ($tables as $table) {
            Schema::dropIfExists($table);
        }
    }
};
