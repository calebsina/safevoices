<?php

namespace App\Models\Report;

use Illuminate\Database\Eloquent\Model;

/**
 * Log of every case code + PIN attempt (success or failure).
 * Brute-force telemetry; written by ResolveFollowUpCase middleware.
 */
class FollowUpAttempt extends Model
{
    public const UPDATED_AT = null; // append-only

    protected $fillable = [
        'reference_code_tried', 'report_id', 'channel_id', 'ip_hash', 'succeeded',
    ];

    protected function casts(): array
    {
        return ['succeeded' => 'boolean'];
    }
}
