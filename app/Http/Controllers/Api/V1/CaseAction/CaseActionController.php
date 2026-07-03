<?php

namespace App\Http\Controllers\Api\V1\CaseAction;

use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\CaseAction\StoreCaseActionRequest;
use App\Http\Resources\CaseAction\CaseActionResource;
use App\Models\Report\Report;
use App\Services\Report\CaseActionService;
use Illuminate\Http\JsonResponse;

/**
 * @group Staff / Case actions
 * @authenticated
 */
class CaseActionController extends BaseController
{
    public function __construct(private readonly CaseActionService $actions) {}

    /** List actions on a case */
    public function index(Report $report): JsonResponse
    {
        $this->authorize('view', $report);

        return $this->ok(CaseActionResource::collection(
            $report->actions()->with('user:id,name')->latest('created_at')->get()
        ));
    }

    /** Record an intervention */
    public function store(StoreCaseActionRequest $request, Report $report): JsonResponse
    {
        $this->authorize('update', $report);

        return $this->created(new CaseActionResource($this->actions->record(
            $report,
            $request->user(),
            $request->validated('action_type'),
            $request->validated('notes'),
        )));
    }
}
