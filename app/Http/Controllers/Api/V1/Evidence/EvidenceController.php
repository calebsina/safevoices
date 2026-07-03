<?php

namespace App\Http\Controllers\Api\V1\Evidence;

use App\Enums\ActorType;
use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\Evidence\StoreEvidenceRequest;
use App\Http\Resources\Evidence\EvidenceResource;
use App\Models\Evidence\Evidence;
use App\Models\Report\Report;
use App\Services\Evidence\EvidenceService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * @group Staff / Evidence
 *
 * Evidence vault access for staff. Every list view and every download
 * writes an audit entry - there is no unaudited path to evidence.
 *
 * @authenticated
 */
class EvidenceController extends BaseController
{
    public function __construct(private readonly EvidenceService $evidence) {}

    /**
     * List evidence for a case
     */
    public function index(Report $report): JsonResponse
    {
        $this->authorize('view', $report);

        return $this->ok(EvidenceResource::collection($this->evidence->forReport($report)));
    }

    /**
     * Attach evidence (staff upload, e.g. scanned document)
     */
    public function store(StoreEvidenceRequest $request, Report $report): JsonResponse
    {
        $this->authorize('update', $report);

        $item = $this->evidence->store($report, $request->file('file'), 'web', ActorType::User);

        return $this->created(new EvidenceResource($item));
    }

    /**
     * Download (decrypt) an evidence file
     *
     * Verifies the integrity hash and writes an `evidence.downloaded`
     * audit entry. CSAM-flagged items refuse download (423-style block)
     * outside the restricted legal workflow.
     */
    public function download(Evidence $evidence): Response
    {
        $this->authorize('view', $evidence->report);

        [$contents, $item] = $this->evidence->download($evidence);

        return response($contents, 200, [
            'Content-Type'        => $item->mime_type,
            'Content-Disposition' => 'attachment; filename="'.addslashes($item->original_filename ?? $item->id).'"',
            'X-Integrity-Hash'    => $item->integrity_hash,
        ]);
    }
}
