<?php

namespace App\Helpers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\ResourceCollection;

/**
 * Single source of truth for the API response envelope.
 *
 * Every endpoint answers with the same shape so front-end and bot
 * clients can handle responses uniformly:
 *
 *  { "success": true|false, "message": "...", "data": ..., "errors": ..., "meta": ... }
 */
class ApiResponse
{
    /**
     * Successful response.
     */
    public static function success(mixed $data = null, ?string $message = null, int $status = 200, array $meta = []): JsonResponse
    {
        $payload = [
            'success' => true,
            'message' => $message ?? __('messages.success'),
            'data'    => $data,
        ];

        if ($meta !== []) {
            $payload['meta'] = $meta;
        }

        return response()->json($payload, $status);
    }

    /**
     * Error response.
     */
    public static function error(string $message, int $status = 400, mixed $errors = null): JsonResponse
    {
        $payload = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors !== null) {
            $payload['errors'] = $errors;
        }

        return response()->json($payload, $status);
    }

    /**
     * Paginated resource collection wrapped in the standard envelope.
     */
    public static function paginated(ResourceCollection $collection, ?string $message = null): JsonResponse
    {
        $paginator = $collection->resource;

        return self::success(
            $collection,
            $message,
            200,
            [
                'current_page' => $paginator->currentPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
                'last_page'    => $paginator->lastPage(),
            ]
        );
    }
}
