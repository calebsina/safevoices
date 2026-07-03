<?php

namespace Database\Seeders;

use App\Models\Notification\NotificationTemplate;
use Illuminate\Database\Seeder;

/**
 * Reporter-facing bodies are deliberately discreet: reference code and
 * neutral status wording only - never case content (safety requirement).
 */
class NotificationTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            [
                'key' => 'status_changed', 'channel' => 'whatsapp',
                'en' => ['subject' => null, 'body' => 'Update on your report {reference_code}: {status}.'],
                'fr' => ['subject' => null, 'body' => 'Mise à jour de votre signalement {reference_code} : {status}.'],
            ],
            [
                'key' => 'case_assigned', 'channel' => 'email',
                'en' => ['subject' => 'Case assigned', 'body' => 'Case {reference_code} has been assigned to you.'],
                'fr' => ['subject' => 'Dossier assigné', 'body' => 'Le dossier {reference_code} vous a été assigné.'],
            ],
            [
                'key' => 'urgent_escalation', 'channel' => 'email',
                'en' => ['subject' => 'URGENT case escalated', 'body' => 'Case {reference_code} was escalated as urgent and requires attention.'],
                'fr' => ['subject' => 'Dossier URGENT escaladé', 'body' => 'Le dossier {reference_code} a été escaladé comme urgent et requiert votre attention.'],
            ],
        ];

        foreach ($templates as $row) {
            $template = NotificationTemplate::updateOrCreate(
                ['key' => $row['key']],
                ['channel' => $row['channel'], 'is_active' => true]
            );

            $template->syncTranslations([
                'en' => $row['en'],
                'fr' => $row['fr'],
            ]);
        }
    }
}
