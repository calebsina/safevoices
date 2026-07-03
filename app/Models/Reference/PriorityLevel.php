<?php

namespace App\Models\Reference;

use App\Traits\Translatable;
use Illuminate\Database\Eloquent\Model;

/**
 * Priority band (urgent/high/medium/low) with the score range that maps
 * to it and its SLA response-time target in minutes.
 */
class PriorityLevel extends Model
{
    use Translatable;

    protected $fillable = ['key', 'score_min', 'score_max', 'sla_minutes', 'color', 'sort_order'];

    /** Resolve the band a computed score falls into. */
    public static function forScore(int $score): ?self
    {
        return static::where('score_min', '<=', $score)
            ->where('score_max', '>=', $score)
            ->first();
    }
}
