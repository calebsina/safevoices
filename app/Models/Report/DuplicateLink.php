<?php

namespace App\Models\Report;

use App\Models\User\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Link between two probably-related reports (system or staff created). */
class DuplicateLink extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = ['report_id', 'linked_report_id', 'confidence', 'linked_by'];

    public function report(): BelongsTo
    {
        return $this->belongsTo(Report::class);
    }

    public function linkedReport(): BelongsTo
    {
        return $this->belongsTo(Report::class, 'linked_report_id');
    }

    public function linker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'linked_by');
    }
}
