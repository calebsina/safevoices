<?php

namespace App\Models\Communication;

use App\Models\Reference\CaseStatus;
use App\Models\Report\Report;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Immutable trail of every status transition (changed_by NULL = system). */
class CaseStatusHistory extends Model
{
    public const UPDATED_AT = null;

    protected $table = 'case_status_history';

    protected $fillable = ['report_id', 'from_status_id', 'to_status_id', 'changed_by', 'note'];

    public function report(): BelongsTo
    {
        return $this->belongsTo(Report::class);
    }

    public function fromStatus(): BelongsTo
    {
        return $this->belongsTo(CaseStatus::class, 'from_status_id');
    }

    public function toStatus(): BelongsTo
    {
        return $this->belongsTo(CaseStatus::class, 'to_status_id');
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
