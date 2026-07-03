<?php

namespace App\Services\Intake;

use App\Helpers\ReferenceCode;
use App\Models\Consent\Consent;
use App\Models\Consent\ConsentVersion;
use App\Models\Reference\CaseStatus;
use App\Models\Reference\Channel;
use App\Models\Report\Report;
use App\Models\Report\ReporterIdentity;
use App\Services\Audit\AuditLogger;
use App\Services\BaseService;
use App\Services\Triage\DuplicateDetectionService;
use App\Services\Triage\PriorityScoringService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Guided intake (module A) - the safety-critical path of the platform.
 *
 * Mirrors the Amie conversation steps: start -> consent -> context ->
 * incident -> submit. Both channels (WhatsApp webhook adapter and web
 * portal) call the same methods, so a report from either channel
 * becomes one record (dossier 3.3).
 */
class IntakeService extends BaseService
{
    protected string $model = Report::class;

    public function __construct(
        private readonly PriorityScoringService $scoring,
        private readonly DuplicateDetectionService $duplicates,
    ) {}

    /**
     * Step 1 - start a draft report (use case 3.2.1).
     * Returns [report, plaintext draft token] - the token is shown once.
     */
    public function start(string $channelKey, string $locale, ?string $phoneE164 = null): array
    {
        return $this->transaction(function () use ($channelKey, $locale, $phoneE164) {
            $channel = Channel::where('key', $channelKey)->firstOrFail();

            $identity = $this->resolveIdentity($channel->id, $locale, $phoneE164);

            $report = Report::create([
                'reference_code'       => ReferenceCode::generate(),
                'reporter_identity_id' => $identity->id,
                'channel_id'           => $channel->id,
                'locale'               => $locale,
            ]);

            // One-off draft token, cached hashed (never persisted in plaintext).
            $token = Str::random(48);
            Cache::put(
                "sv.intake.draft.{$report->id}",
                hash('sha256', $token),
                now()->addHours(config('safevoice.intake.draft_ttl_hours'))
            );

            return [$report, $token];
        });
    }

    /** Step 2 - consent & language (use case 3.2.2). */
    public function captureConsent(Report $report, bool $dataUseConsent, bool $contactConsent): Report
    {
        $version = ConsentVersion::active();

        if (! $version) {
            throw ValidationException::withMessages(['consent' => __('messages.intake.no_consent_version')]);
        }

        return $this->transaction(function () use ($report, $version, $dataUseConsent, $contactConsent) {
            Consent::create([
                'report_id'          => $report->id,
                'consent_version_id' => $version->id,
                'locale'             => $report->locale,
                'data_use_consent'   => $dataUseConsent,
                'contact_consent'    => $contactConsent,
                'captured_at'        => now(),
            ]);

            // Contact preference is honoured platform-wide via the identity.
            $report->reporterIdentity()->update(['contact_consent' => $contactConsent]);

            return $report;
        });
    }

    /** Step 3 - case context (use case 3.2.3). */
    public function captureContext(Report $report, array $data): Report
    {
        $report->update([
            'affected_person_type_id' => $data['affected_person_type_id'],
            'relationship_id'         => $data['relationship_id'],
            'reporting_for'           => $data['reporting_for'],
        ]);

        return $report;
    }

    /** Step 4 - incident description (use case 3.2.4). */
    public function captureIncident(Report $report, array $data): Report
    {
        $report->update([
            'category_id'   => $data['category_id'],
            'description'   => $data['description'],          // reporter's own words, never translated
            'incident_area' => $data['incident_area'] ?? null,
            'incident_at'   => $data['incident_at'] ?? null,
            'is_urgent'     => (bool) ($data['is_imminent_danger'] ?? false),
        ]);

        return $report;
    }

    /**
     * Step 5 - submit & issue code + PIN (use case 3.2.5).
     * The PIN is returned ONCE in plaintext; only its hash is stored.
     * There is no recovery path (deliberate anonymity trade-off) - the
     * client must tell the reporter to save both.
     */
    public function submit(Report $report): array
    {
        if (! $report->consents()->exists() || ! $report->category_id || ! $report->description) {
            throw ValidationException::withMessages(['report' => __('messages.intake.incomplete')]);
        }

        $pin = ReferenceCode::pin();

        $report = $this->transaction(function () use ($report, $pin) {
            $report->forceFill([
                'pin_hash'     => Hash::make($pin),
                'status_id'    => CaseStatus::byKey(CaseStatus::SUBMITTED)->id,
                'submitted_at' => now(),
            ])->save();

            $report->statusHistory()->create([
                'to_status_id' => $report->status_id,
                'note'         => 'Report submitted via '.$report->channel?->key,
            ]);

            return $report;
        });

        // Post-submit triage automation (module D).
        $this->scoring->score($report);
        $this->duplicates->detect($report);

        Cache::forget("sv.intake.draft.{$report->id}");
        AuditLogger::log('report.submitted', $report, actorType: \App\Enums\ActorType::Reporter);

        $sla = $report->refresh()->priorityLevel?->sla_minutes;

        return [
            'reference_code'      => $report->reference_code,
            'pin'                 => $pin,
            'expected_response'   => $sla ? now()->addMinutes($sla)->diffForHumans() : null,
        ];
    }

    /**
     * Find or create the tokenised identity. The raw number is hashed
     * for dedup and encrypted for delivery only - it never appears in
     * any staff-facing payload.
     */
    private function resolveIdentity(int $channelId, string $locale, ?string $phoneE164): ReporterIdentity
    {
        if ($phoneE164 === null) {
            return ReporterIdentity::create([
                'channel_id'    => $channelId,
                'locale'        => $locale,
                'first_seen_at' => now(),
                'last_seen_at'  => now(),
            ]);
        }

        $hash = ReporterIdentity::hashPhone($phoneE164);

        $identity = ReporterIdentity::firstOrCreate(
            ['phone_hash' => $hash],
            [
                'channel_id'      => $channelId,
                'phone_encrypted' => $phoneE164, // 'encrypted' cast handles at-rest encryption
                'locale'          => $locale,
                'first_seen_at'   => now(),
            ]
        );

        $identity->update(['last_seen_at' => now(), 'locale' => $locale]);

        return $identity;
    }
}
