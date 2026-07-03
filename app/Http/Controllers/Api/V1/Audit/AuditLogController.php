<?php

namespace App\Http\Controllers\Api\V1\Audit;

use App\Http\Controllers\Api\BaseController;
use App\Http\Resources\Audit\AuditLogResource;
use App\Services\Audit\AuditLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @group Admin / Audit trail
 *
 * Read-only. Requires permission `audit.view`. The trail is append-only
 * at the application layer - there is no update or delete endpoint.
 *
 * @authenticated
 */
class AuditLogController extends BaseController
{
    public function __construct(private readonly AuditLogService $audit) {}

    /**
     * Search audit entries
     *
     * @queryParam action string Prefix filter, e.g. evidence. Example: evidence.
     * @queryParam user_id string Staff uuid. Example: 9d3c...
     * @queryParam from string ISO date. Example: 2026-07-01
     * @queryParam to string ISO date. Example: 2026-07-02
     */
    public function index(Request $request): JsonResponse
    {
        return $this->paginated(AuditLogResource::collection(
            $this->audit->search($request->query(), (int) $request->query('per_page', 25))
        ));
    }
}
