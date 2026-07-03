<?php

namespace App\Http\Controllers\Api\V1\Report;

use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\Report\AssignRequest;
use App\Http\Requests\Report\EscalateRequest;
use App\Http\Requests\Report\UpdateStatusRequest;
use App\Http\Resources\Report\ReportResource;
use App\Models\Report\Report;
use App\Services\Report\ReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @group Staff / Cases
 *
 * Staff-side case operations. Row-level visibility follows the role
 * matrix: caseworkers see assigned cases, supervisors their unit,
 * administrators everything - enforced by ReportPolicy + query scope.
 *
 * @authenticated
 */
class ReportController extends BaseController
{
    public function __construct(private readonly ReportService $reports) {}

    /**
     * Case queue
     *
     * @queryParam status_id integer Filter by status. Example: 1
     * @queryParam priority_level_id integer Filter by priority. Example: 1
     * @queryParam category_id integer Filter by category. Example: 2
     * @queryParam is_urgent boolean Urgent only. Example: true
     * @queryParam unassigned boolean Unassigned only (triage view). Example: true
     * @queryParam search string Reference code search. Example: SV-7F
     * @queryParam per_page integer Page size (max 100). Example: 15
     */
    public function index(Request $request): JsonResponse
    {
        $paginator = $this->reports->queue(
            $request->user(),
            $request->query(),
            (int) $request->query('per_page', 15),
        );

        return $this->paginated(ReportResource::collection($paginator));
    }

    /**
     * Case detail
     */
    public function show(Report $report): JsonResponse
    {
        $this->authorize('view', $report);

        return $this->ok(new ReportResource($this->reports->show($report)));
    }

    /**
     * Update status
     *
     * Writes immutable case_status_history and (with consent) sends the
     * reporter a discreet status notification.
     */
    public function updateStatus(UpdateStatusRequest $request, Report $report): JsonResponse
    {
        $this->authorize('update', $report);

        return $this->ok(new ReportResource($this->reports->updateStatus(
            $report,
            $request->validated('status_id'),
            $request->validated('note'),
            $request->user(),
        )));
    }

    /**
     * Assign case
     *
     * Supervisor/administrator action (permission: case.assign).
     */
    public function assign(AssignRequest $request, Report $report): JsonResponse
    {
        $this->authorize('assign', $report);

        return $this->ok(new ReportResource($this->reports->assign(
            $report,
            $request->validated('assigned_to'),
            $request->validated('office_id'),
            $request->user(),
        )));
    }

    /**
     * Escalate as urgent
     *
     * Flags the case urgent and alerts unit supervisors.
     */
    public function escalate(EscalateRequest $request, Report $report): JsonResponse
    {
        $this->authorize('update', $report);

        return $this->ok(new ReportResource($this->reports->escalate(
            $report,
            $request->validated('reason'),
            $request->user(),
        )));
    }
}
