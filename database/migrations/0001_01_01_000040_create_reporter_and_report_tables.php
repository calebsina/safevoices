<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Reporters & cases (Appendix 2, section 6).
 * The core case record is `reports` - never `cases` (reserved keyword).
 */
return new class extends Migration
{
    public function up(): void
    {
        // The tokenised reporter - the ONLY link to a real phone number.
        Schema::create('reporter_identities', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('channel_id')->constrained('channels');
            $table->string('phone_hash', 64)->nullable()->index(); // (hash) SHA-256, dedup only
            $table->text('phone_encrypted')->nullable();           // (lock) messaging layer only
            $table->string('locale', 5);
            $table->boolean('contact_consent')->default(false);
            $table->timestamp('first_seen_at')->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamps();
            $table->foreign('locale')->references('code')->on('locales');
        });

        Schema::create('reports', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('reference_code', 12)->unique();        // SV-7F3K-9Q2
            $table->string('pin_hash')->nullable();                // set on submit
            $table->foreignUuid('reporter_identity_id')->constrained('reporter_identities');
            $table->foreignId('channel_id')->constrained('channels');
            $table->string('locale', 5);                           // language of the report
            $table->foreignId('affected_person_type_id')->nullable()->constrained('affected_person_types');
            $table->foreignId('relationship_id')->nullable()->constrained('relationships');
            $table->string('reporting_for', 10)->nullable();       // self / other
            $table->foreignId('category_id')->nullable()->constrained('case_categories');
            $table->text('description')->nullable();               // reporter's own words - NOT translated
            $table->string('incident_area')->nullable();
            $table->timestamp('incident_at')->nullable();
            $table->foreignId('priority_level_id')->nullable()->constrained('priority_levels');
            $table->integer('priority_score')->nullable();
            $table->boolean('is_urgent')->default(false);
            // Nullable while the report is a draft; set to `submitted` on finalisation.
            $table->foreignId('status_id')->nullable()->constrained('case_statuses');
            $table->foreignUuid('assigned_to')->nullable()->constrained('users');
            $table->foreignId('office_id')->nullable()->constrained('offices');
            $table->foreignUuid('linked_parent_report_id')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('locale')->references('code')->on('locales');
            $table->index(['status_id', 'priority_level_id']);
            $table->index(['assigned_to', 'office_id']);
        });

        Schema::table('reports', function (Blueprint $table) {
            $table->foreign('linked_parent_report_id')->references('id')->on('reports');
        });

        // Brute-force protection log on code + PIN follow-up.
        Schema::create('follow_up_attempts', function (Blueprint $table) {
            $table->id();
            $table->string('reference_code_tried', 12)->index();
            $table->foreignUuid('report_id')->nullable()->constrained('reports');
            $table->foreignId('channel_id')->nullable()->constrained('channels');
            $table->string('ip_hash', 64)->nullable();             // (hash)
            $table->boolean('succeeded');
            $table->timestamp('created_at')->index();
        });

        // Duplicate detection links (Appendix 2, section 9).
        Schema::create('duplicate_links', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('report_id')->constrained('reports')->cascadeOnDelete();
            $table->foreignUuid('linked_report_id')->constrained('reports')->cascadeOnDelete();
            $table->integer('confidence');                         // 0-100 match score
            $table->foreignUuid('linked_by')->nullable()->constrained('users'); // NULL = system
            $table->timestamp('created_at');
            $table->unique(['report_id', 'linked_report_id']);
        });
    }

    public function down(): void
    {
        foreach (['duplicate_links', 'follow_up_attempts', 'reports', 'reporter_identities'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
