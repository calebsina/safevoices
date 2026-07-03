<?php

namespace App\Services\Triage;

use App\Models\Reference\PriorityLevel;
use App\Models\Reference\PriorityRule;
use App\Models\Report\Report;

/**
 * Auto-prioritisation (use case 3.2.12).
 *
 * Scores a submitted report against admin-configurable priority_rules
 * and maps the total onto a priority_levels band. The rules table keeps
 * the algorithm tunable by MINAS without a deploy; the signal
 * implementations below cover the four seeded rules from the dossier
 * (imminent danger, severity, recency, victim vulnerability).
 */
class PriorityScoringService
{
    public function score(Report $report): Report
    {
        $report->loadMissing(['category', 'affectedPersonType']);

        $total = PriorityRule::where('is_active', true)->get()
            ->sum(fn (PriorityRule $rule) => $rule->weight * $this->signalPoints($rule, $report));

        $level = PriorityLevel::forScore($total);

        $report->forceFill([
            'priority_score'    => $total,
            'priority_level_id' => $level?->id,
            'is_urgent'         => $report->is_urgent || $level?->key === 'urgent',
        ])->save();

        return $report;
    }

    /**
     * Evaluate one rule's signal against the report.
     * `conditions` example: {"signal": "severity", "points": 10}
     */
    private function signalPoints(PriorityRule $rule, Report $report): int
    {
        $conditions = $rule->conditions;
        $points = (int) ($conditions['points'] ?? 0);

        return match ($conditions['signal'] ?? $rule->key) {
            // Reporter or bot flagged immediate danger during intake.
            'imminent_danger' => $report->is_urgent ? $points : 0,

            // Category severity weight from reference data.
            'severity' => ($report->category?->severity_weight ?? 0),

            // Incident happened within the configured recency window.
            'recency' => $report->incident_at
                && $report->incident_at->gt(now()->subHours(config('safevoice.triage.recent_incident_hours')))
                ? $points : 0,

            // A child is affected.
            'victim_vulnerability' => $report->affectedPersonType?->key === 'child' ? $points : 0,

            default => 0,
        };
    }
}
