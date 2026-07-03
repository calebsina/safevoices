<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Evidence vault (Appendix 2, section 7).
 * Every read/download MUST write an audit_logs entry - enforced in
 * EvidenceService. CSAM-flagged rows are locked to a restricted
 * legal-handling workflow.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evidence', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('report_id')->constrained('reports')->cascadeOnDelete();
            $table->string('storage_disk', 50);
            $table->text('storage_path');                   // (lock) no identity in the object key
            $table->text('original_filename')->nullable();  // (lock) may leak identity
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('file_size');        // bytes
            $table->string('integrity_hash', 64);           // (hash) SHA-256 tamper-evidence
            $table->foreignId('source_channel_id')->constrained('channels');
            $table->string('scan_status', 10)->default('pending'); // pending/clean/flagged/error
            $table->boolean('is_csam_flagged')->default(false);
            $table->timestamp('uploaded_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evidence');
    }
};
