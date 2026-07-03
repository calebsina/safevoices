<?php

namespace App\Models\Setting;

use App\Traits\Translatable;
use Illuminate\Database\Eloquent\Model;

/**
 * Platform setting. Non-translatable values live in `value`; if
 * is_translatable, the per-locale text lives in setting_translations
 * (e.g. the public tagline).
 */
class Setting extends Model
{
    use Translatable;

    protected $fillable = ['key', 'group', 'type', 'value', 'is_translatable'];

    protected function casts(): array
    {
        return ['is_translatable' => 'boolean'];
    }

    /** Resolve the effective value for the current locale + declared type. */
    public function resolvedValue(): mixed
    {
        $raw = $this->is_translatable ? $this->t('value') : $this->value;

        return match ($this->type) {
            'bool'  => filter_var($raw, FILTER_VALIDATE_BOOLEAN),
            'int'   => (int) $raw,
            'json'  => json_decode((string) $raw, true),
            default => $raw,
        };
    }
}
