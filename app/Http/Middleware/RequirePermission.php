<?php

namespace App\Http\Middleware;

use App\Helpers\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * RBAC gate: `->middleware('permission:case.assign')`.
 *
 * Permissions are attached to roles (permission_role pivot) exactly as
 * described in the data dictionary. Row-level scoping (Own / Assigned /
 * Unit) is NOT handled here - that lives in Policies (e.g. ReportPolicy),
 * keeping the matrix of section 3.5 of the dossier intact.
 */
class RequirePermission
{
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = $request->user();

        if (! $user || ! $user->hasPermission($permission)) {
            return ApiResponse::error(__('messages.forbidden'), 403);
        }

        return $next($request);
    }
}
