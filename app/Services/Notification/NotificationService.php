<?php

namespace App\Services\Notification;

use App\Enums\NotificationStatus;
use App\Models\Notification\Notification;
use App\Models\Notification\NotificationTemplate;
use App\Models\Report\Report;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

/**
 * Notifications & alerts (module F).
 *
 * Renders a template translation in the recipient's locale and records
 * a `notifications` row. Actual delivery through the WhatsApp Cloud API
 * / BSP or the SMS gateway plugs into deliver() - kept as a single
 * integration point so the WABA choice (dossier 2.3) doesn't leak into
 * business code.
 *
 * SAFETY: reporter-facing payloads must stay discreet - status wording
 * only, never case content, and only when contact_consent is true.
 */
class NotificationService
{
    /** Discreet status update to the reporter (use case 3.2.20). */
    public function notifyReporterStatusChanged(Report $report): void
    {
        $identity = $report->reporterIdentity;

        // Consent is enforced here, once, for every reporter notification.
        if (! $identity || ! $identity->contact_consent) {
            return;
        }

        $this->dispatch(
            templateKey: 'status_changed',
            notifiable: $identity,
            locale: $identity->locale,
            placeholders: [
                // Reference code only - NEVER case content in a reporter message.
                'reference_code' => $report->reference_code,
                'status'         => $report->status?->t('reporter_label', $identity->locale),
            ],
        );
    }

    /** Staff alert on assignment / escalation (use case 3.2.21). */
    public function notifyStaff(User $user, string $templateKey, Report $report): void
    {
        $this->dispatch(
            templateKey: $templateKey,
            notifiable: $user,
            locale: app()->getLocale(),
            placeholders: ['reference_code' => $report->reference_code],
        );
    }

    private function dispatch(string $templateKey, Model $notifiable, string $locale, array $placeholders): void
    {
        $template = NotificationTemplate::with('translations')
            ->where('key', $templateKey)
            ->where('is_active', true)
            ->first();

        if (! $template) {
            Log::warning("SafeVoice: notification template [$templateKey] missing.");

            return;
        }

        $body = strtr((string) $template->t('body', $locale), $this->wrap($placeholders));

        $notification = Notification::create([
            'notifiable_type' => $notifiable->getTable(),
            'notifiable_id'   => $notifiable->getKey(),
            'template_id'     => $template->id,
            'channel'         => $template->channel,
            'payload'         => ['body' => $body, 'subject' => $template->t('subject', $locale)],
            'status'          => NotificationStatus::Queued,
        ]);

        $this->deliver($notification);
    }

    /**
     * INTEGRATION POINT - WhatsApp Cloud API / BSP / SMS gateway.
     * Swap the log call for the real transport; update status on the
     * provider callback (delivered/failed webhooks).
     */
    protected function deliver(Notification $notification): void
    {
        Log::info('SafeVoice notification queued', [
            'id'      => $notification->id,
            'channel' => $notification->channel,
        ]);
    }

    private function wrap(array $placeholders): array
    {
        $wrapped = [];

        foreach ($placeholders as $key => $value) {
            $wrapped['{'.$key.'}'] = (string) $value;
        }

        return $wrapped;
    }
}
