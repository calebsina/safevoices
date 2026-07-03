<?php

namespace App\Http\Controllers\Api\V1\Reference;

use App\Http\Controllers\Api\BaseController;
use App\Http\Resources\Reference\CaseStatusResource;
use App\Services\Reference\CaseStatusService;
use Illuminate\Http\JsonResponse;

/**
 * @group Reference data
 */
class CaseStatusController extends BaseController
{
    public function __construct(private readonly CaseStatusService $statuses) {}

    /**
     * List case statuses (localized, staff + reporter labels)
     *
     * @unauthenticated
     */
    public function index(): JsonResponse
    {
        return $this->ok(CaseStatusResource::collection(
            $this->statuses->query()->orderBy('sort_order')->get()
        ));
    }
}
