<?php

namespace App\Models\Localization;

use Illuminate\Database\Eloquent\Model;

/**
 * Supported platform languages (seed: en, fr). Adding a locale row is
 * all that is required to enable a new language - no migration needed.
 */
class Locale extends Model
{
    protected $fillable = ['code', 'name', 'is_default', 'is_active', 'sort_order'];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
            'is_active'  => 'boolean',
        ];
    }
}
