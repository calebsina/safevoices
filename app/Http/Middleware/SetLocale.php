<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Locale negotiation for every API request.
 *
 * Resolution order:
 *  1. ?lang= query parameter          (explicit, e.g. bot callbacks)
 *  2. X-Locale header                 (SPA / dashboard)
 *  3. Accept-Language header          (browsers)
 *  4. config('app.locale') fallback
 *
 * Only locales that exist and are active in the `locales` table are
 * accepted, so an attacker cannot force an unknown locale string
 * into translations lookups.
 */
class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $requested = $request->query('lang')
            ?? $request->header('X-Locale')
            ?? $request->getPreferredLanguage(sv_locales());

        $locale = in_array($requested, sv_locales(), true)
            ? $requested
            : config('app.locale', 'en');

        app()->setLocale($locale);

        return $next($request);
    }
}
