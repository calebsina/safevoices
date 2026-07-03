<?php

namespace App\Http\Controllers\Api\V1\FollowUp;

use App\Enums\ActorType;
use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\Evidence\StoreEvidenceRequest;
use App\Http\Requests\FollowUp\AddInformationRequest;
use App\Http\Requests\FollowUp\ReplyRequest;
use App\Http\Resources\Evidence\EvidenceResource;
use App\Http\Resources\Message\CaseMessageResource;
use App\Services\Evidence\EvidenceService;
use App\Services\FollowUp\FollowUpService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @group Public / Anonymous follow-up
 *
 * Per-case authentication for reporters: every request carries
 * `X-Case-Code` + `X-Case-Pin` headers (issued at submission).
 * Attempts are throttled and logged; wrong code and wrong PIN return
 * an identical error on purpose.
 */
class FollowUpController extends BaseController
{
    public function __construct(
        private readonly FollowUpService $followUp,
        private readonly EvidenceService $evidence,
    ) {}

    /**
     * Case status (reporter view)
     *
     * @unauthenticated
     * @header X-Case-Code SV-7F3K-9Q2
     * @header X-Case-Pin 482913
     * @response 200 {"success":true,"data":{"reference_code":"SV-7F3K-9Q2","status":"Being reviewed","unread_messages":1}}
     */
    public function status(Request $request): JsonResponse
    {
        return $this->ok($this->followUp->status($request->attributes->get('follow_up_report')));
    }

    /**
     * Add information
     *
     * @unauthenticated
     * @header X-Case-Code SV-7F3K-9Q2
     * @header X-Case-Pin 482913
     */
    public function addInformation(AddInformationRequest $request): JsonResponse
    {
        $message = $this->followUp->addInformation(
            $request->attributes->get('follow_up_report'),
            $request->validated('body'),
        );

        return $this->created(new CaseMessageResource($message));
    }

    /**
     * Message thread
     *
     * Staff names are stripped: the reporter only sees sender_type.
     *
     * @unauthenticated
     * @header X-Case-Code SV-7F3K-9Q2
     * @header X-Case-Pin 482913
     */
    public function messages(Request $request): JsonResponse
    {
        $messages = $this->followUp->messages($request->attributes->get('follow_up_report'));

        return $this->ok($messages->map(fn ($m) => [
            'sender_type' => $m->sender_type,
            'body'        => $m->body,
            'created_at'  => $m->created_at?->toIso8601String(),
        ]));
    }

    /**
     * Reply in the thread
     *
     * @unauthenticated
     * @header X-Case-Code SV-7F3K-9Q2
     * @header X-Case-Pin 482913
     */
    public function reply(ReplyRequest $request): JsonResponse
    {
        $message = $this->followUp->reply(
            $request->attributes->get('follow_up_report'),
            $request->validated('body'),
        );

        return $this->created(new CaseMessageResource($message));
    }

    /**
     * Attach evidence after submission
     *
     * @unauthenticated
     * @header X-Case-Code SV-7F3K-9Q2
     * @header X-Case-Pin 482913
     * @bodyParam file file required The evidence file (image/video/audio/document).
     */
    public function uploadEvidence(StoreEvidenceRequest $request): JsonResponse
    {
        // Bound either by the follow-up middleware or the intake draft middleware.
        $report = $request->attributes->get('follow_up_report')
            ?? $request->attributes->get('draft_report');

        $evidence = $this->evidence->store(
            $report,
            $request->file('file'),
            $report->channel?->key ?? 'web',
            ActorType::Reporter,
        );

        return $this->created(new EvidenceResource($evidence));
    }
}
