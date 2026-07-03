<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Supported languages - every *_translations.locale references this.
        Schema::create('locales', function (Blueprint $table) {
            $table->id();
            $table->string('code', 5)->unique();     // en, fr
            $table->string('name', 50);              // "English", "Français"
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // MINAS units / regions.
        Schema::create('offices', function (Blueprint $table) {
            $table->id();
            $table->string('key', 64)->unique();     // yaounde-central
            $table->string('region', 100)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('office_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('office_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 5);
            $table->string('name', 150);
            $table->unique(['office_id', 'locale']);
            $table->foreign('locale')->references('code')->on('locales');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('office_translations');
        Schema::dropIfExists('offices');
        Schema::dropIfExists('locales');
    }
};
