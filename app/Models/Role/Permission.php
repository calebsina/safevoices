<?php

namespace App\Models\Role;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/** Atomic ability key (case.assign, evidence.view, cms.manage, ...). */
class Permission extends Model
{
    protected $fillable = ['key', 'group'];

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }
}
