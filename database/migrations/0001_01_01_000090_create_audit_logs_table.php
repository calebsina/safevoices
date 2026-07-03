<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Audit trail (Appendix 2, section 12).
 * Append-only: rows are never updated and never soft-deleted.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->string('actor_type', 10);               // user / reporter / system
            $table->foreignUuid('user_id')->nullable()->constrained('users');
            $table->string('action', 100)->index();         // evidence.viewed, case.assigned, ...
            $table->string('auditable_type', 50)->nullable();
            $table->string('auditable_id', 36)->nullable(); // uuid or bigint as string
            $table->string('description', 500)->nullable();
            $table->string('ip_hash', 64)->nullable();      // (hash)
            $table->string('user_agent')->nullable();
            $table->jsonb('metadata')->nullable();
            $table->timestamp('created_at')->index();
            $table->index(['auditable_type', 'auditable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
