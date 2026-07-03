<?php

namespace App\Http\Middleware;

use App\Helpers\ApiResponse;
use App\Models\Report\FollowUpAttempt;
use App\Models\Report\Report;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

/**
 * Anonymous follow-up authentication (case code + PIN).
 *
 * Reporters have no account: every follow-up request carries
 *   X-Case-Code: SV-7F3K-9Q2
 *   X-Case-Pin:  123456
 *
 * Responsibilities:
 *  - throttle attempts (brute-force protection, config safevoice.follow_up)
 *  - log EVERY attempt to follow_up_attempts (success or failure)
 *  - never reveal whether the code or the PIN was the wrong part
 *  - bind the resolved Report to the request as "follow_up_report"
 */
class ResolveFollowUpCase
{
    public function handle(Request $request, Closure $next): Response
    {
        $code = strtoupper(trim((string) $request->header('X-Case-Code')));
        $pin  = (string) $request->header('X-Case-Pin');

        if ($code === '' || $pin === '') {
            return ApiResponse::error(__('messages.follow_up.credentials_required'), 401);
        }

        // Throttle by (hashed IP + code) so one attacker cannot lock out a code globally.
        $throttleKey = 'follow-up:'.sha1($request->ip().'|'.$code);
        $config = config('safevoice.follow_up');

        if (RateLimiter::tooManyAttempts($throttleKey, $config['max_attempts'])) {
            return ApiResponse::error(__('messages.follow_up.too_many_attempts'), 429);
        }

        RateLimiter::hit($throttleKey, $config['decay_seconds']);

        $report = Report::where('reference_code', $code)->first();
        $succeeded = $report !== null && Hash::check($pin, $report->pin_hash);

        // Audit trail of every attempt, matched or not (section 6 of the dictionary).
        FollowUpAttempt::create([
            'reference_code_tried' => $code,
            'report_id'            => $succeeded ? $report->id : null,
            'channel_id'           => $request->attributes->get('channel_id'),
            'ip_hash'              => hash('sha256', (string) $request->ip()),
            'succeeded'            => $succeeded,
        ]);

        if (! $succeeded) {
            // Deliberately identical message for "unknown code" and "wrong PIN".
            return ApiResponse::error(__('messages.follow_up.invalid_credentials'), 401);
        }

        RateLimiter::clear($throttleKey);
        $request->attributes->set('follow_up_report', $report);

        return $next($request);
    }
}
