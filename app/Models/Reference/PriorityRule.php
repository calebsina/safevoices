<?php

namespace App\Models\Reference;

use Illuminate\Database\Eloquent\Model;

/**
 * Admin-configurable scoring rule. `conditions` (jsonb) maps a signal
 * to points; `weight` multiplies the signal contribution. Consumed by
 * PriorityScoringService - NOT translatable (pure config).
 */
class PriorityRule extends Model
{
    protected $fillable = ['key', 'weight', 'conditions', 'is_active'];

    protected function casts(): array
    {
        return [
            'conditions' => 'array',
            'is_active'  => 'boolean',
        ];
    }
}
