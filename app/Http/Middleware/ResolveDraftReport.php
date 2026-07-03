<?php

namespace App\Http\Middleware;

use App\Helpers\ApiResponse;
use App\Models\Report\Report;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

/**
 * Protects the multi-step intake: when a report is started, the client
 * receives a one-off draft token (cached, TTL safevoice.intake.draft_ttl_hours).
 * Each subsequent intake step must present it as X-Draft-Token together
 * with the report id in the route, so nobody can write into someone
 * else's unfinished report.
 */
class ResolveDraftReport
{
    public function handle(Request $request, Closure $next): Response
    {
        $report = $request->route('report');

        if (is_string($report)) {
            $report = Report::find($report);
        }

        $token = (string) $request->header('X-Draft-Token');
        $expected = $report ? Cache::get("sv.intake.draft.{$report->id}") : null;

        if (! $report || $report->submitted_at !== null || ! $expected || ! hash_equals($expected, hash('sha256', $token))) {
            return ApiResponse::error(__('messages.intake.invalid_draft'), 401);
        }

        $request->attributes->set('draft_report', $report);

        return $next($request);
    }
}
