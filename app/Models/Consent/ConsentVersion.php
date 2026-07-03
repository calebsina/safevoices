<?php

namespace App\Models\Consent;

use App\Traits\Translatable;
use Illuminate\Database\Eloquent\Model;

/** Versioned consent text (CMS-editable, EN/FR through translations). */
class ConsentVersion extends Model
{
    use Translatable;

    protected $fillable = ['version', 'effective_from', 'is_active'];

    protected function casts(): array
    {
        return [
            'effective_from' => 'datetime',
            'is_active'      => 'boolean',
        ];
    }

    public static function active(): ?self
    {
        return static::where('is_active', true)->latest('effective_from')->first();
    }
}
