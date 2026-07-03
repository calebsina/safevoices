<?php

namespace App\Models\Reference;

use App\Traits\Translatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Incident category (physical_abuse, sexual_abuse, neglect, gbv, ...).
 * Self-nesting through parent_id; severity_weight feeds priority scoring.
 * Translatable: name, description.
 */
class CaseCategory extends Model
{
    use Translatable;

    protected $fillable = ['key', 'parent_id', 'severity_weight', 'sort_order', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }
}
