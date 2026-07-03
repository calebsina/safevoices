<?php

namespace Database\Seeders;

use App\Models\Reference\PriorityRule;
use Illuminate\Database\Seeder;

/**
 * The four scoring signals implemented in PriorityScoringService.
 * severity draws its points from case_categories.severity_weight.
 */
class PriorityRuleSeeder extends Seeder
{
    public function run(): void
    {
        $rules = [
            ['key' => 'imminent_danger',      'conditions' => ['signal' => 'imminent_danger', 'points' => 50]],
            ['key' => 'severity',             'conditions' => ['signal' => 'severity']],
            ['key' => 'recency',              'conditions' => ['signal' => 'recency', 'points' => 15]],
            ['key' => 'victim_vulnerability', 'conditions' => ['signal' => 'victim_vulnerability', 'points' => 20]],
        ];

        foreach ($rules as $rule) {
            PriorityRule::updateOrCreate(
                ['key' => $rule['key']],
                ['weight' => 1, 'conditions' => $rule['conditions'], 'is_active' => true]
            );
        }
    }
}
