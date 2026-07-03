<?php

namespace Database\Seeders;

use App\Models\Reference\CaseStatus;
use Illuminate\Database\Seeder;

/**
 * The 7-state case lifecycle. reporter_label is deliberately simpler
 * and calmer than the staff label (data dictionary section 5).
 */
class CaseStatusSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = [
            ['key' => 'submitted',    'terminal' => false, 'color' => '#64748b',
             'en' => ['Submitted', 'Received'],            'fr' => ['Soumis', 'Reçu']],
            ['key' => 'triaged',      'terminal' => false, 'color' => '#0ea5e9',
             'en' => ['Triaged', 'Being reviewed'],        'fr' => ['Trié', 'En cours d\'examen']],
            ['key' => 'assigned',     'terminal' => false, 'color' => '#8b5cf6',
             'en' => ['Assigned', 'Being reviewed'],       'fr' => ['Assigné', 'En cours d\'examen']],
            ['key' => 'in_progress',  'terminal' => false, 'color' => '#f59e0b',
             'en' => ['In progress', 'Being handled'],     'fr' => ['En cours', 'En cours de traitement']],
            ['key' => 'action_taken', 'terminal' => false, 'color' => '#10b981',
             'en' => ['Action taken', 'Action was taken'], 'fr' => ['Action menée', 'Une action a été menée']],
            ['key' => 'referred',     'terminal' => false, 'color' => '#06b6d4',
             'en' => ['Referred', 'Referred to a support service'], 'fr' => ['Référé', 'Orienté vers un service d\'appui']],
            ['key' => 'resolved',     'terminal' => true,  'color' => '#22c55e',
             'en' => ['Resolved', 'Closed'],               'fr' => ['Résolu', 'Clôturé']],
        ];

        foreach ($statuses as $i => $row) {
            $status = CaseStatus::updateOrCreate(
                ['key' => $row['key']],
                ['sort_order' => $i + 1, 'is_terminal' => $row['terminal'], 'color' => $row['color']]
            );

            $status->syncTranslations([
                'en' => ['label' => $row['en'][0], 'reporter_label' => $row['en'][1]],
                'fr' => ['label' => $row['fr'][0], 'reporter_label' => $row['fr'][1]],
            ]);
        }
    }
}
