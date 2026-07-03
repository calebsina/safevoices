<?php

use App\Helpers\ApiResponse;
use App\Http\Middleware\RequirePermission;
use App\Http\Middleware\ResolveDraftReport;
use App\Http\Middleware\ResolveFollowUpCase;
use App\Http\Middleware\SetLocale;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/*
|--------------------------------------------------------------------------
| Application bootstrap (Laravel 11+/12 style)
|--------------------------------------------------------------------------
| Registers routing, middleware aliases and a JSON-first exception
| renderer so the API always answers with the standard envelope
| produced by App\Helpers\ApiResponse.
*/
return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__.'/../routes/api.php',
        web: __DIR__.'/../routes/web.php',
        apiPrefix: 'api',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Locale negotiation runs on every API request.
        $middleware->api(prepend: [SetLocale::class]);

        $middleware->alias([
            'permission' => RequirePermission::class, // permission:users.manage
            'follow-up' => ResolveFollowUpCase::class, // case code + PIN auth
            'draft' => ResolveDraftReport::class,  // intake draft token auth
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Force a consistent JSON error envelope for API consumers.
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson()
        );

        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->is('api/*')) {
                return ApiResponse::error(__('messages.unauthenticated'), 401);
            }
        });

        $exceptions->render(function (AuthorizationException $e, Request $request) {
            if ($request->is('api/*')) {
                return ApiResponse::error(__('messages.forbidden'), 403);
            }
        });

        $exceptions->render(function (ModelNotFoundException|NotFoundHttpException $e, Request $request) {
            if ($request->is('api/*')) {
                return ApiResponse::error(__('messages.not_found'), 404);
            }
        });
    })->create();
