<?php

namespace App\Http\Controllers\Api\V1\Intake;

use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\Intake\ConsentRequest;
use App\Http\Requests\Intake\ContextRequest;
use App\Http\Requests\Intake\IncidentRequest;
use App\Http\Requests\Intake\StartIntakeRequest;
use App\Services\Intake\IntakeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @group Public / Report intake
 *
 * Guided, anonymous report intake. Mirrors the Amie conversation:
 * start -> consent -> context -> incident -> submit. Both the WhatsApp
 * webhook adapter and the web portal call these same endpoints.
 *
 * After `start`, every step requires the `X-Draft-Token` header
 * returned by `start` (drafts expire after 24h). No account, no login.
 */
class IntakeController extends BaseController
{
    public function __construct(private readonly IntakeService $intake) {}

    /**
     * Start a report (draft)
     *
     * @unauthenticated
     * @bodyParam channel string required Channel key: whatsapp, web, sms, ussd. Example: web
     * @bodyParam locale string required Locale code (en, fr). Example: fr
     * @bodyParam phone string E.164 phone; omit if the reporter declines contact. Example: +237650000000
     * @response 201 {"success":true,"message":"Created","data":{"report_id":"9d3c...","draft_token":"a1B2..."}}
     */
    public function start(StartIntakeRequest $request): JsonResponse
    {
        [$report, $token] = $this->intake->start(
            $request->validated('channel'),
            $request->validated('locale'),
            $request->validated('phone'),
        );

        return $this->created([
            'report_id'   => $report->id,
            'draft_token' => $token, // shown once - the client must keep it for the next steps
        ]);
    }

    /**
     * Step: consent
     *
     * @unauthenticated
     * @header X-Draft-Token a1B2c3...
     * @bodyParam data_use_consent boolean required Must be true to proceed. Example: true
     * @bodyParam contact_consent boolean required May be false (no notifications). Example: true
     */
    public function consent(ConsentRequest $request): JsonResponse
    {
        $report = $request->attributes->get('draft_report');

        $this->intake->captureConsent(
            $report,
            $request->validated('data_use_consent'),
            $request->validated('contact_consent'),
        );

        return $this->ok(['report_id' => $report->id, 'step' => 'consent']);
    }

    /**
     * Step: case context
     *
     * @unauthenticated
     * @header X-Draft-Token a1B2c3...
     */
    public function context(ContextRequest $request): JsonResponse
    {
        $report = $request->attributes->get('draft_report');
        $this->intake->captureContext($report, $request->validated());

        return $this->ok(['report_id' => $report->id, 'step' => 'context']);
    }

    /**
     * Step: incident description
     *
     * @unauthenticated
     * @header X-Draft-Token a1B2c3...
     * @bodyParam category_id integer required Case category. Example: 1
     * @bodyParam description string required The reporter's own words. Example: ...
     * @bodyParam incident_area string Approximate area only - no exact address required. Example: Yaoundé IV
     * @bodyParam incident_at string ISO datetime. Example: 2026-06-30T18:00:00Z
     * @bodyParam is_imminent_danger boolean Flags the report urgent. Example: false
     */
    public function incident(IncidentRequest $request): JsonResponse
    {
        $report = $request->attributes->get('draft_report');
        $this->intake->captureIncident($report, $request->validated());

        return $this->ok(['report_id' => $report->id, 'step' => 'incident']);
    }

    /**
     * Submit the report
     *
     * Returns the case reference code and the PIN **exactly once**.
     * There is no recovery path (anonymity trade-off): the client must
     * instruct the reporter to save both.
     *
     * @unauthenticated
     * @header X-Draft-Token a1B2c3...
     * @response 200 {"success":true,"data":{"reference_code":"SV-7F3K-9Q2","pin":"482913","expected_response":"in 2 hours"}}
     */
    public function submit(Request $request): JsonResponse
    {
        return $this->ok($this->intake->submit($request->attributes->get('draft_report')));
    }
}
