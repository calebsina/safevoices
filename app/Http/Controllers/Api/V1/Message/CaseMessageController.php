<?php

namespace App\Http\Controllers\Api\V1\Message;

use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\Message\StoreMessageRequest;
use App\Http\Resources\Message\CaseMessageResource;
use App\Models\Report\Report;
use App\Services\Message\CaseMessageService;
use Illuminate\Http\JsonResponse;

/**
 * @group Staff / Case messages
 *
 * Staff side of the anonymous two-way channel. Staff address the case,
 * never a person; delivery is routed through the tokenised identity.
 *
 * @authenticated
 */
class CaseMessageController extends BaseController
{
    public function __construct(private readonly CaseMessageService $messages) {}

    /** Thread */
    public function index(Report $report): JsonResponse
    {
        $this->authorize('view', $report);

        return $this->ok(CaseMessageResource::collection($this->messages->thread($report)));
    }

    /** Send message to the reporter */
    public function store(StoreMessageRequest $request, Report $report): JsonResponse
    {
        $this->authorize('update', $report);

        return $this->created(new CaseMessageResource(
            $this->messages->send($report, $request->user(), $request->validated('body'))
        ));
    }
}
