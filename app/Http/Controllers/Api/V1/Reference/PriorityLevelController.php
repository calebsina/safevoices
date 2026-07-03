<?php

namespace App\Http\Controllers\Api\V1\Reference;

use App\Http\Controllers\Api\BaseController;
use App\Http\Resources\Reference\PriorityLevelResource;
use App\Services\Reference\PriorityLevelService;
use Illuminate\Http\JsonResponse;

/**
 * @group Reference data
 * @authenticated
 */
class PriorityLevelController extends BaseController
{
    public function __construct(private readonly PriorityLevelService $levels) {}

    /** List priority levels */
    public function index(): JsonResponse
    {
        return $this->ok(PriorityLevelResource::collection(
            $this->levels->query()->orderBy('sort_order')->get()
        ));
    }
}
