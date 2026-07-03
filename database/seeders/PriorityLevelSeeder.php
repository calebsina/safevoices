<?php

namespace Database\Seeders;

use App\Models\Reference\PriorityLevel;
use Illuminate\Database\Seeder;

class PriorityLevelSeeder extends Seeder
{
    public function run(): void
    {
        $levels = [
            ['key' => 'urgent', 'min' => 80, 'max' => 999, 'sla' => 120,  'color' => '#dc2626', 'en' => 'Urgent', 'fr' => 'Urgent'],
            ['key' => 'high',   'min' => 55, 'max' => 79,  'sla' => 480,  'color' => '#ea580c', 'en' => 'High',   'fr' => 'Élevée'],
            ['key' => 'medium', 'min' => 30, 'max' => 54,  'sla' => 1440, 'color' => '#ca8a04', 'en' => 'Medium', 'fr' => 'Moyenne'],
            ['key' => 'low',    'min' => 0,  'max' => 29,  'sla' => 4320, 'color' => '#16a34a', 'en' => 'Low',    'fr' => 'Faible'],
        ];

        foreach ($levels as $i => $row) {
            $level = PriorityLevel::updateOrCreate(
                ['key' => $row['key']],
                ['score_min' => $row['min'], 'score_max' => $row['max'], 'sla_minutes' => $row['sla'], 'color' => $row['color'], 'sort_order' => $i + 1]
            );

            $level->syncTranslations([
                'en' => ['label' => $row['en']],
                'fr' => ['label' => $row['fr']],
            ]);
        }
    }
}
