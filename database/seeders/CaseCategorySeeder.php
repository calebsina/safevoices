<?php

namespace Database\Seeders;

use App\Models\Reference\CaseCategory;
use Illuminate\Database\Seeder;

class CaseCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['key' => 'physical_abuse', 'weight' => 30, 'en' => 'Physical abuse',        'fr' => 'Violence physique'],
            ['key' => 'sexual_abuse',   'weight' => 40, 'en' => 'Sexual abuse',          'fr' => 'Violence sexuelle'],
            ['key' => 'neglect',        'weight' => 20, 'en' => 'Neglect',               'fr' => 'Négligence'],
            ['key' => 'gbv',            'weight' => 35, 'en' => 'Gender-based violence', 'fr' => 'Violence basée sur le genre'],
            ['key' => 'other',          'weight' => 10, 'en' => 'Other',                 'fr' => 'Autre'],
        ];

        foreach ($categories as $i => $row) {
            $category = CaseCategory::updateOrCreate(
                ['key' => $row['key']],
                ['severity_weight' => $row['weight'], 'sort_order' => $i + 1, 'is_active' => true]
            );

            $category->syncTranslations([
                'en' => ['name' => $row['en']],
                'fr' => ['name' => $row['fr']],
            ]);
        }
    }
}
