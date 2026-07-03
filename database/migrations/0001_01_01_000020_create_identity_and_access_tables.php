<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Staff identity & RBAC (Appendix 2, section 4). Reporters have no
 * account: they authenticate per-case with a reference code + PIN.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('key', 64)->unique();   // caseworker, supervisor, administrator
            $table->boolean('is_system')->default(false); // protects core roles
            $table->timestamps();
        });

        Schema::create('role_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 5);
            $table->string('label', 100);
            $table->string('description', 255)->nullable();
            $table->unique(['role_id', 'locale']);
            $table->foreign('locale')->references('code')->on('locales');
        });

        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('key', 100)->unique();  // case.assign, evidence.view
            $table->string('group', 64)->nullable();
            $table->timestamps();
        });

        Schema::create('permission_role', function (Blueprint $table) {
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->foreignId('permission_id')->constrained()->cascadeOnDelete();
            $table->primary(['role_id', 'permission_id']);
        });

        // uuid PK: staff IDs are non-enumerable in API URLs.
        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('office_id')->nullable()->constrained();
            $table->foreignId('role_id')->constrained();
            $table->string('name', 150);           // internal only
            $table->string('email', 190)->unique();
            $table->string('phone', 30)->nullable();
            $table->string('password');            // bcrypt/argon hash
            $table->boolean('mfa_enabled')->default(false);
            $table->text('mfa_secret')->nullable(); // (lock) app-layer encrypted TOTP secret
            $table->boolean('is_active')->default(true); // deactivate instead of delete
            $table->timestamp('last_login_at')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        foreach (['users', 'permission_role', 'permissions', 'role_translations', 'roles'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
