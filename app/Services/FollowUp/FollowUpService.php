<?php

namespace App\Services\FollowUp;

use App\Enums\ActorType;
use App\Enums\SenderType;
use App\Models\Communication\CaseMessage;
use App\Models\Report\Report;
use App\Services\Audit\AuditLogger;

/**
 * Anonymous follow-up (module C). Every method assumes the report was
 * already authenticated by the ResolveFollowUpCase middleware
 * (code + PIN), so this layer only implements the actions.
 */
class FollowUpService
{
    /** Simplified status view for the reporter (use case 3.2.9). */
    public function status(Report $report): array
    {
        $report->loadMissing(['status.translations', 'messages']);

        return [
            'reference_code' => $report->reference_code,
            // reporter_label: the calm, simplified wording - not staff jargon.
            'status'         => $report->status?->t('reporter_label'),
            'submitted_at'   => $report->submitted_at?->toIso8601String(),
            'unread_messages' => $report->messages
                ->where('sender_type', SenderType::Staff)
                ->where('is_read', false)
                ->count(),
        ];
    }

    /** Add information after submission (use case 3.2.10). */
    public function addInformation(Report $report, string $body): CaseMessage
    {
        $message = $report->messages()->create([
            'sender_type' => SenderType::Reporter,
            'body'        => $body,
            'locale'      => $report->locale,
        ]);

        AuditLogger::log('report.information_added', $report, actorType: ActorType::Reporter);

        return $message;
    }

    /** Messages thread, marking staff messages as read (use case 3.2.11). */
    public function messages(Report $report)
    {
        $report->messages()
            ->where('sender_type', SenderType::Staff)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return $report->messages()->orderBy('created_at')->get();
    }

    public function reply(Report $report, string $body): CaseMessage
    {
        return $report->messages()->create([
            'sender_type' => SenderType::Reporter,
            'body'        => $body,
            'locale'      => $report->locale,
        ]);
    }
}
