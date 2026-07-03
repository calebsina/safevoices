<?php

namespace App\Services\Report;

use App\Models\Communication\Referral;
use App\Models\Reference\CaseStatus;
use App\Models\Report\Report;
use App\Models\User\User;
use App\Services\Audit\AuditLogger;
use App\Services\BaseService;

/** Onward referrals to partner services (module E). */
class ReferralService extends BaseService
{
    protected string $model = Referral::class;

    public function refer(Report $report, User $user, array $data): Referral
    {
        return $this->transaction(function () use ($report, $user, $data) {
            $referral = $report->referrals()->create([
                'partner_type_id' => $data['partner_type_id'],
                'partner_name'    => $data['partner_name'] ?? null,
                'referred_by'     => $user->id,
                'notes'           => $data['notes'] ?? null,
                'referred_at'     => now(),
            ]);

            // Referral moves the case to "referred" per the lifecycle.
            $referred = CaseStatus::byKey(CaseStatus::REFERRED);

            if ($report->status_id !== $referred->id) {
                $report->statusHistory()->create([
                    'from_status_id' => $report->status_id,
                    'to_status_id'   => $referred->id,
                    'changed_by'     => $user->id,
                    'note'           => 'Referred to partner',
                ]);
                $report->update(['status_id' => $referred->id]);
            }

            AuditLogger::log('case.referred', $report);

            return $referral->load('partnerType.translations');
        });
    }

    public function updateStatus(Referral $referral, string $status, ?string $notes): Referral
    {
        $referral->update(['status' => $status, 'notes' => $notes ?? $referral->notes]);

        return $referral;
    }
}
