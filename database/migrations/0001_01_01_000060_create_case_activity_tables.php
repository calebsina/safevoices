<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Communication & case activity (Appendix 2, section 8). */
return new class extends Migration
{
    public function up(): void
    {
        // Two-way reporter <-> caseworker messages. Single-locale bodies.
        Schema::create('case_messages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('report_id')->constrained('reports')->cascadeOnDelete();
            $table->string('sender_type', 10);              // reporter / staff / system
            $table->foreignUuid('sender_user_id')->nullable()->constrained('users');
            $table->text('body');
            $table->string('locale', 5);
            $table->boolean('is_read')->default(false);
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('created_at');
            $table->foreign('locale')->references('code')->on('locales');
            $table->index(['report_id', 'created_at']);
        });

        Schema::create('case_status_history', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('report_id')->constrained('reports')->cascadeOnDelete();
            $table->foreignId('from_status_id')->nullable()->constrained('case_statuses');
            $table->foreignId('to_status_id')->constrained('case_statuses');
            $table->foreignUuid('changed_by')->nullable()->constrained('users'); // NULL = system
            $table->string('note', 500)->nullable();
            $table->timestamp('created_at');
        });

        Schema::create('case_actions', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('report_id')->constrained('reports')->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained('users');
            $table->string('action_type', 64);              // home_visit, phone_followup, ...
            $table->text('notes')->nullable();
            $table->timestamp('created_at');
        });

        Schema::create('referrals', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('report_id')->constrained('reports')->cascadeOnDelete();
            $table->foreignId('partner_type_id')->constrained('referral_partner_types');
            $table->string('partner_name', 150)->nullable();
            $table->foreignUuid('referred_by')->constrained('users');
            $table->string('status', 10)->default('pending'); // pending/accepted/completed/declined
            $table->text('notes')->nullable();
            $table->timestamp('referred_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['referrals', 'case_actions', 'case_status_history', 'case_messages'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
