<?php

namespace App\Http\Controllers\Api\V1\Referral;

use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\Referral\StoreReferralRequest;
use App\Http\Requests\Referral\UpdateReferralRequest;
use App\Http\Resources\Referral\ReferralResource;
use App\Models\Communication\Referral;
use App\Models\Report\Report;
use App\Services\Report\ReferralService;
use Illuminate\Http\JsonResponse;

/**
 * @group Staff / Referrals
 * @authenticated
 */
class ReferralController extends BaseController
{
    public function __construct(private readonly ReferralService $referrals) {}

    /** List referrals on a case */
    public function index(Report $report): JsonResponse
    {
        $this->authorize('view', $report);

        return $this->ok(ReferralResource::collection(
            $report->referrals()->with('partnerType.translations')->latest('referred_at')->get()
        ));
    }

    /** Refer to a partner (moves the case to "referred") */
    public function store(StoreReferralRequest $request, Report $report): JsonResponse
    {
        $this->authorize('update', $report);

        return $this->created(new ReferralResource(
            $this->referrals->refer($report, $request->user(), $request->validated())
        ));
    }

    /** Update referral outcome */
    public function update(UpdateReferralRequest $request, Report $report, Referral $referral): JsonResponse
    {
        $this->authorize('update', $report);

        abort_unless($referral->report_id === $report->id, 404);

        return $this->ok(new ReferralResource($this->referrals->updateStatus(
            $referral,
            $request->validated('status'),
            $request->validated('notes'),
        )));
    }
}
