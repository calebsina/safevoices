<?php

namespace App\Models\Role;

use App\Traits\Translatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Cache;

/**
 * Staff role (caseworker, supervisor, administrator).
 * Translatable: label, description. is_system rows cannot be deleted.
 */
class Role extends Model
{
    use Translatable;

    public const CASEWORKER    = 'caseworker';
    public const SUPERVISOR    = 'supervisor';
    public const ADMINISTRATOR = 'administrator';

    protected $fillable = ['key', 'is_system'];

    protected function casts(): array
    {
        return ['is_system' => 'boolean'];
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class);
    }

    /**
     * Cached permission keys for fast RBAC checks
     * (flushed by RoleService on every write).
     */
    public function permissionKeys(): array
    {
        return Cache::rememberForever(
            "sv.role.{$this->id}.permissions",
            fn () => $this->permissions()->pluck('key')->all()
        );
    }
}
