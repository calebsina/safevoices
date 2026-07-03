<?php

namespace App\Models\Communication;

use App\Enums\SenderType;
use App\Models\Report\Report;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Two-way, identity-free message between reporter and caseworker.
 * body is single-locale (never translated) - it is someone's own words.
 */
class CaseMessage extends Model
{
    use HasUuids;

    public const UPDATED_AT = null;

    protected $fillable = [
        'report_id', 'sender_type', 'sender_user_id',
        'body', 'locale', 'is_read', 'delivered_at',
    ];

    protected function casts(): array
    {
        return [
            'sender_type'  => SenderType::class,
            'is_read'      => 'boolean',
            'delivered_at' => 'datetime',
        ];
    }

    public function report(): BelongsTo
    {
        return $this->belongsTo(Report::class);
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_user_id');
    }
}
