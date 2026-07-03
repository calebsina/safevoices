<?php

namespace App\Models\Communication;

use App\Models\Report\Report;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Intervention recorded by a caseworker (home_visit, phone_followup, ...). */
class CaseAction extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = ['report_id', 'user_id', 'action_type', 'notes'];

    public function report(): BelongsTo
    {
        return $this->belongsTo(Report::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
