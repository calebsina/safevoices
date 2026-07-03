<?php

namespace App\Models\Communication;

use App\Enums\ReferralStatus;
use App\Models\Reference\ReferralPartnerType;
use App\Models\Report\Report;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Onward referral to a health / legal / psychosocial / police partner. */
class Referral extends Model
{
    protected $fillable = [
        'report_id', 'partner_type_id', 'partner_name',
        'referred_by', 'status', 'notes', 'referred_at',
    ];

    protected function casts(): array
    {
        return [
            'status'      => ReferralStatus::class,
            'referred_at' => 'datetime',
        ];
    }

    public function report(): BelongsTo
    {
        return $this->belongsTo(Report::class);
    }

    public function partnerType(): BelongsTo
    {
        return $this->belongsTo(ReferralPartnerType::class, 'partner_type_id');
    }

    public function referredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referred_by');
    }
}
