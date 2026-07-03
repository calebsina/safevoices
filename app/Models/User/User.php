<?php

namespace App\Models\User;

use App\Models\Office\Office;
use App\Models\Role\Role;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

/**
 * Staff account (caseworker / supervisor / administrator).
 *
 * Reporters deliberately have NO account: they authenticate per case
 * with a reference code + PIN (see ResolveFollowUpCase middleware).
 *
 * UUID primary key so staff IDs are non-enumerable in API URLs.
 */
class User extends Authenticatable implements JWTSubject
{
    use HasUuids, Notifiable, SoftDeletes;

    protected $fillable = [
        'office_id', 'role_id', 'name', 'email', 'phone',
        'password', 'mfa_enabled', 'mfa_secret', 'is_active',
        'last_login_at', 'email_verified_at',
    ];

    protected $hidden = ['password', 'remember_token', 'mfa_secret'];

    protected function casts(): array
    {
        return [
            'password'          => 'hashed',
            'mfa_enabled'       => 'boolean',
            'mfa_secret'        => 'encrypted', // (lock) column - app-layer encrypted TOTP secret
            'is_active'         => 'boolean',
            'last_login_at'     => 'datetime',
            'email_verified_at' => 'datetime',
        ];
    }

    // ------------------------------------------------------------------
    // Relations
    // ------------------------------------------------------------------

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class);
    }

    // ------------------------------------------------------------------
    // RBAC helpers
    // ------------------------------------------------------------------

    public function hasRole(string $roleKey): bool
    {
        return $this->role?->key === $roleKey;
    }

    public function isAdministrator(): bool
    {
        return $this->hasRole(Role::ADMINISTRATOR);
    }

    public function isSupervisor(): bool
    {
        return $this->hasRole(Role::SUPERVISOR);
    }

    public function isCaseworker(): bool
    {
        return $this->hasRole(Role::CASEWORKER);
    }

    public function hasPermission(string $permission): bool
    {
        return in_array($permission, $this->role?->permissionKeys() ?? [], true);
    }

    // ------------------------------------------------------------------
    // JWTSubject (tymon/jwt-auth)
    // ------------------------------------------------------------------

    public function getJWTIdentifier(): mixed
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims(): array
    {
        // Role in the token lets the SPA gate menus without an extra call.
        return ['role' => $this->role?->key];
    }
}
