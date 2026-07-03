<?php

namespace App\Services\Message;

use App\Enums\SenderType;
use App\Models\Communication\CaseMessage;
use App\Models\Report\Report;
use App\Models\User\User;
use App\Services\Audit\AuditLogger;
use App\Services\BaseService;

/**
 * Staff side of the anonymous two-way channel (use case 3.2.18).
 * Staff replies address the case, never a person - the identity wall
 * stays intact because delivery goes through the tokenised identity.
 */
class CaseMessageService extends BaseService
{
    protected string $model = CaseMessage::class;

    public function thread(Report $report)
    {
        // Mark reporter messages as read by staff.
        $report->messages()
            ->where('sender_type', SenderType::Reporter)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return $report->messages()->with('sender:id,name')->orderBy('created_at')->get();
    }

    public function send(Report $report, User $staff, string $body): CaseMessage
    {
        $message = $report->messages()->create([
            'sender_type'    => SenderType::Staff,
            'sender_user_id' => $staff->id,
            'body'           => $body,
            'locale'         => $report->locale, // reply in the reporter's language
        ]);

        AuditLogger::log('case.message_sent', $report);

        return $message;
    }
}
