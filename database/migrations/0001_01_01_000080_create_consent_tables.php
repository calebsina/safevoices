<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Consent (Appendix 2, section 11). */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consent_versions', function (Blueprint $table) {
            $table->id();
            $table->string('version', 20)->unique();        // e.g. 2026-06
            $table->timestamp('effective_from');
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });

        Schema::create('consent_version_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('consent_version_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 5);
            $table->text('body');                           // consent text shown to the reporter
            $table->unique(['consent_version_id', 'locale']);
            $table->foreign('locale')->references('code')->on('locales');
        });

        Schema::create('consents', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('report_id')->constrained('reports')->cascadeOnDelete();
            $table->foreignId('consent_version_id')->constrained('consent_versions');
            $table->string('locale', 5);                    // language shown
            $table->boolean('data_use_consent');
            $table->boolean('contact_consent');             // drives notifications
            $table->timestamp('captured_at');
            $table->timestamp('created_at');
            $table->foreign('locale')->references('code')->on('locales');
        });
    }

    public function down(): void
    {
        foreach (['consents', 'consent_version_translations', 'consent_versions'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
