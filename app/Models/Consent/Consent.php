<?php

namespace App\Models\Consent;

use App\Models\Report\Report;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Consent captured on a specific report: exactly which text version was
 * shown, in which language, and what the reporter agreed to.
 * contact_consent drives every later notification decision.
 */
class Consent extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'report_id', 'consent_version_id', 'locale',
        'data_use_consent', 'contact_consent', 'captured_at',
    ];

    protected function casts(): array
    {
        return [
            'data_use_consent' => 'boolean',
            'contact_consent'  => 'boolean',
            'captured_at'      => 'datetime',
        ];
    }

    public function report(): BelongsTo
    {
        return $this->belongsTo(Report::class);
    }

    public function version(): BelongsTo
    {
        return $this->belongsTo(ConsentVersion::class, 'consent_version_id');
    }
}
