<?php

use App\Http\Controllers\Api\V1;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| SafeVoice API v1
|--------------------------------------------------------------------------
| Three access tiers:
|   1. Public         - locales, reference lists, CMS, intake, follow-up
|   2. Staff (JWT)    - case operations, scoped by ReportPolicy
|   3. Admin (JWT +   - management areas, gated by permission middleware
|      permission)
|
| SetLocale runs on every request (prepended in bootstrap/app.php).
*/

Route::prefix('v1')->group(function () {

    /* ---------------------------- PUBLIC ---------------------------- */

    Route::get('locales', [V1\Locale\LocaleController::class, 'index']);

    Route::prefix('reference')->group(function () {
        Route::get('categories', [V1\Reference\CaseCategoryController::class, 'index']);
        Route::get('statuses', [V1\Reference\CaseStatusController::class, 'index']);
        Route::get('lookups/{type}', [V1\Reference\LookupController::class, 'index']);
    });

    Route::prefix('cms')->group(function () {
        Route::get('pages/{slug}', [V1\Cms\PageController::class, 'showPublic']);
        Route::get('menus/{key}', [V1\Cms\MenuController::class, 'show']);
        Route::get('faqs', [V1\Cms\FaqController::class, 'index']);
        Route::get('ui-strings', [V1\Setting\UiStringController::class, 'map']);
        Route::get('settings', [V1\Setting\SettingController::class, 'publicIndex']);
    });

    Route::get('consent/active', [V1\Consent\ConsentVersionController::class, 'active']);

    // Guided intake. Steps after `start` carry X-Draft-Token.
    Route::prefix('intake')->group(function () {
        Route::post('start', [V1\Intake\IntakeController::class, 'start'])
            ->middleware('throttle:10,1');

        Route::middleware('draft')->group(function () {
            Route::patch('{report}/consent', [V1\Intake\IntakeController::class, 'consent']);
            Route::patch('{report}/context', [V1\Intake\IntakeController::class, 'context']);
            Route::patch('{report}/incident', [V1\Intake\IntakeController::class, 'incident']);
            Route::post('{report}/evidence', [V1\FollowUp\FollowUpController::class, 'uploadEvidence']);
            Route::post('{report}/submit', [V1\Intake\IntakeController::class, 'submit']);
        });
    });

    // Anonymous follow-up. Every route carries X-Case-Code + X-Case-Pin.
    Route::prefix('follow-up')->middleware('follow-up')->group(function () {
        Route::get('status', [V1\FollowUp\FollowUpController::class, 'status']);
        Route::post('information', [V1\FollowUp\FollowUpController::class, 'addInformation']);
        Route::get('messages', [V1\FollowUp\FollowUpController::class, 'messages']);
        Route::post('messages', [V1\FollowUp\FollowUpController::class, 'reply']);
        Route::post('evidence', [V1\FollowUp\FollowUpController::class, 'uploadEvidence']);
    });

    /* ------------------------- STAFF AUTH --------------------------- */

    Route::prefix('auth')->group(function () {
        Route::post('login', [V1\Auth\AuthController::class, 'login'])->middleware('throttle:10,1');
        Route::post('refresh', [V1\Auth\AuthController::class, 'refresh']);

        Route::middleware('auth:api')->group(function () {
            Route::get('me', [V1\Auth\AuthController::class, 'me']);
            Route::post('logout', [V1\Auth\AuthController::class, 'logout']);
        });
    });

    /* ------------------------ STAFF (JWT) --------------------------- */

    Route::middleware('auth:api')->group(function () {

        // Case operations - row-level scope enforced by ReportPolicy.
        Route::prefix('reports')->group(function () {
            Route::get('/', [V1\Report\ReportController::class, 'index']);
            Route::get('{report}', [V1\Report\ReportController::class, 'show']);
            Route::patch('{report}/status', [V1\Report\ReportController::class, 'updateStatus'])
                ->middleware('permission:case.update_status');
            Route::patch('{report}/assign', [V1\Report\ReportController::class, 'assign'])
                ->middleware('permission:case.assign');
            Route::post('{report}/escalate', [V1\Report\ReportController::class, 'escalate'])
                ->middleware('permission:case.escalate');

            Route::get('{report}/evidence', [V1\Evidence\EvidenceController::class, 'index'])
                ->middleware('permission:evidence.view');
            Route::post('{report}/evidence', [V1\Evidence\EvidenceController::class, 'store'])
                ->middleware('permission:evidence.upload');

            Route::get('{report}/messages', [V1\Message\CaseMessageController::class, 'index']);
            Route::post('{report}/messages', [V1\Message\CaseMessageController::class, 'store'])
                ->middleware('permission:case.message');

            Route::get('{report}/actions', [V1\CaseAction\CaseActionController::class, 'index']);
            Route::post('{report}/actions', [V1\CaseAction\CaseActionController::class, 'store'])
                ->middleware('permission:case.action');

            Route::get('{report}/referrals', [V1\Referral\ReferralController::class, 'index']);
            Route::post('{report}/referrals', [V1\Referral\ReferralController::class, 'store'])
                ->middleware('permission:case.refer');
            Route::patch('{report}/referrals/{referral}', [V1\Referral\ReferralController::class, 'update'])
                ->middleware('permission:case.refer');
        });

        Route::get('evidence/{evidence}/download', [V1\Evidence\EvidenceController::class, 'download'])
            ->middleware('permission:evidence.download');

        /* -------------------- ADMIN (permission) -------------------- */

        Route::middleware('permission:users.manage')->group(function () {
            Route::apiResource('users', V1\User\UserController::class);
        });

        Route::middleware('permission:roles.manage')->group(function () {
            Route::get('roles/permissions', [V1\Role\RoleController::class, 'permissions']);
            Route::apiResource('roles', V1\Role\RoleController::class);
        });

        Route::middleware('permission:offices.manage')->group(function () {
            Route::apiResource('offices', V1\Office\OfficeController::class);
        });

        Route::middleware('permission:localization.manage')->group(function () {
            Route::post('locales', [V1\Locale\LocaleController::class, 'store']);
            Route::patch('locales/{locale}', [V1\Locale\LocaleController::class, 'update']);
        });

        Route::middleware('permission:reference.manage')->prefix('reference')->group(function () {
            Route::post('categories', [V1\Reference\CaseCategoryController::class, 'store']);
            Route::patch('categories/{category}', [V1\Reference\CaseCategoryController::class, 'update']);
            Route::delete('categories/{category}', [V1\Reference\CaseCategoryController::class, 'destroy']);
            Route::get('priorities', [V1\Reference\PriorityLevelController::class, 'index']);
            Route::post('lookups/{type}', [V1\Reference\LookupController::class, 'store']);
            Route::patch('lookups/{type}/{id}', [V1\Reference\LookupController::class, 'update']);
            Route::delete('lookups/{type}/{id}', [V1\Reference\LookupController::class, 'destroy']);
        });

        Route::middleware('permission:audit.view')->group(function () {
            Route::get('audit-logs', [V1\Audit\AuditLogController::class, 'index']);
        });

        Route::middleware('permission:cms.manage')->prefix('cms')->group(function () {
            Route::apiResource('pages', V1\Cms\PageController::class)->except('show');
            Route::get('pages/{page}', [V1\Cms\PageController::class, 'show'])->whereNumber('page');
            Route::post('faqs', [V1\Cms\FaqController::class, 'store']);
            Route::patch('faqs/{faq}', [V1\Cms\FaqController::class, 'update']);
            Route::delete('faqs/{faq}', [V1\Cms\FaqController::class, 'destroy']);
            Route::get('ui-strings/all', [V1\Setting\UiStringController::class, 'index']);
            Route::post('ui-strings', [V1\Setting\UiStringController::class, 'store']);
            Route::patch('ui-strings/{ui_string}', [V1\Setting\UiStringController::class, 'update']);
        });

        Route::middleware('permission:settings.manage')->group(function () {
            Route::get('settings', [V1\Setting\SettingController::class, 'index']);
            Route::patch('settings/{setting}', [V1\Setting\SettingController::class, 'update']);
            Route::get('notification-templates', [V1\Notification\NotificationTemplateController::class, 'index']);
            Route::post('notification-templates', [V1\Notification\NotificationTemplateController::class, 'store']);
            Route::patch('notification-templates/{template}', [V1\Notification\NotificationTemplateController::class, 'update']);
            Route::get('consent-versions', [V1\Consent\ConsentVersionController::class, 'index']);
            Route::post('consent-versions', [V1\Consent\ConsentVersionController::class, 'store']);
        });
    });
});
