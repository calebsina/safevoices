<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Notifications & templates (Appendix 2, section 10). */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_templates', function (Blueprint $table) {
            $table->id();
            $table->string('key', 100)->unique();           // status_changed, case_assigned, ...
            $table->string('channel', 10);                  // whatsapp/sms/email/push/portal
            $table->string('whatsapp_template_name', 150)->nullable(); // Meta-approved name
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('notification_template_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('notification_template_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 5);
            $table->string('subject')->nullable();          // email / push
            $table->text('body');                           // discreet - must not expose case content
            $table->unique(['notification_template_id', 'locale'], 'notif_tpl_locale_unique');
            $table->foreign('locale')->references('code')->on('locales');
        });

        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('notifiable_type', 50);          // user or reporter_identity
            $table->uuid('notifiable_id');
            $table->foreignId('template_id')->nullable()->constrained('notification_templates');
            $table->string('channel', 20);
            $table->jsonb('payload');                       // rendered; no sensitive reporter content
            $table->string('status', 10)->default('queued');
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            $table->index(['notifiable_type', 'notifiable_id']);
        });
    }

    public function down(): void
    {
        foreach (['notifications', 'notification_template_translations', 'notification_templates'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
