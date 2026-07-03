<?php

namespace App\Services\Triage;

use App\Models\Report\DuplicateLink;
use App\Models\Report\Report;

/**
 * Duplicate detection & linking (use case 3.2.13).
 *
 * Compares a newly submitted report to recent reports using cheap,
 * privacy-preserving signals (tokenised phone hash, category, area
 * words) and records probable matches in duplicate_links. Links are
 * advisory - a supervisor confirms or merges during triage.
 */
class DuplicateDetectionService
{
    public function detect(Report $report): void
    {
        $windowDays = (int) config('safevoice.triage.duplicate_window_days');
        $minConfidence = (int) config('safevoice.triage.duplicate_min_confidence');

        $candidates = Report::query()
            ->submitted()
            ->where('id', '!=', $report->id)
            ->where('submitted_at', '>=', now()->subDays($windowDays))
            ->with('reporterIdentity')
            ->get();

        foreach ($candidates as $candidate) {
            $confidence = $this->confidence($report, $candidate);

            if ($confidence >= $minConfidence) {
                DuplicateLink::firstOrCreate(
                    ['report_id' => $report->id, 'linked_report_id' => $candidate->id],
                    ['confidence' => $confidence, 'linked_by' => null]
                );
            }
        }
    }

    /** Naive weighted score, 0-100. Deliberately conservative. */
    private function confidence(Report $a, Report $b): int
    {
        $score = 0;

        // Same tokenised phone = same reporter, strong signal.
        if ($a->reporterIdentity?->phone_hash
            && $a->reporterIdentity->phone_hash === $b->reporterIdentity?->phone_hash) {
            $score += 50;
        }

        if ($a->category_id && $a->category_id === $b->category_id) {
            $score += 20;
        }

        // Rough area overlap on normalised words.
        if ($a->incident_area && $b->incident_area) {
            $wordsA = array_filter(preg_split('/\W+/u', mb_strtolower($a->incident_area)));
            $wordsB = array_filter(preg_split('/\W+/u', mb_strtolower($b->incident_area)));

            if ($wordsA && count(array_intersect($wordsA, $wordsB)) > 0) {
                $score += 30;
            }
        }

        return min($score, 100);
    }
}
