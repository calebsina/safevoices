<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Settings & DB-managed UI strings (Appendix 2, section 14). */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key', 100)->unique();       // site.tagline, whatsapp.number
            $table->string('group', 64)->nullable();
            $table->string('type', 20)->default('string'); // string/bool/json/int
            $table->text('value')->nullable();          // non-translatable config value
            $table->boolean('is_translatable')->default(false);
            $table->timestamps();
        });

        Schema::create('setting_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('setting_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 5);
            $table->text('value');
            $table->unique(['setting_id', 'locale']);
            $table->foreign('locale')->references('code')->on('locales');
        });

        Schema::create('ui_strings', function (Blueprint $table) {
            $table->id();
            $table->string('key', 150)->unique();       // portal.followup.title, bot.greeting
            $table->string('group', 64)->nullable();
            $table->timestamps();
        });

        Schema::create('ui_string_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ui_string_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 5);
            $table->text('value');
            $table->unique(['ui_string_id', 'locale']);
            $table->foreign('locale')->references('code')->on('locales');
        });
    }

    public function down(): void
    {
        foreach (['ui_string_translations', 'ui_strings', 'setting_translations', 'settings'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
