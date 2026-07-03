<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\ResourceCollection;

/**
 * Base class for every API controller.
 *
 * Controllers stay thin: validate through a FormRequest, delegate to a
 * Service, transform through a Resource, and answer through the
 * envelope helpers below. No business logic lives in controllers.
 */
abstract class BaseController extends Controller
{
    use AuthorizesRequests;

    protected function ok(mixed $data = null, ?string $message = null, array $meta = []): JsonResponse
    {
        return ApiResponse::success($data, $message, 200, $meta);
    }

    protected function created(mixed $data = null, ?string $message = null): JsonResponse
    {
        return ApiResponse::success($data, $message ?? __('messages.created'), 201);
    }

    protected function deleted(?string $message = null): JsonResponse
    {
        return ApiResponse::success(null, $message ?? __('messages.deleted'));
    }

    protected function fail(string $message, int $status = 400, mixed $errors = null): JsonResponse
    {
        return ApiResponse::error($message, $status, $errors);
    }

    protected function paginated(ResourceCollection $collection, ?string $message = null): JsonResponse
    {
        return ApiResponse::paginated($collection, $message);
    }
}
