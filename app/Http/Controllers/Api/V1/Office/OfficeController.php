<?php

namespace App\Http\Controllers\Api\V1\Office;

use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\Office\StoreOfficeRequest;
use App\Http\Requests\Office\UpdateOfficeRequest;
use App\Http\Resources\Office\OfficeResource;
use App\Models\Office\Office;
use App\Services\Office\OfficeService;
use Illuminate\Http\JsonResponse;

/**
 * @group Admin / Offices
 * @authenticated
 */
class OfficeController extends BaseController
{
    public function __construct(private readonly OfficeService $offices) {}

    /** List offices */
    public function index(): JsonResponse
    {
        return $this->ok(OfficeResource::collection($this->offices->query()->get()));
    }

    /** Create office */
    public function store(StoreOfficeRequest $request): JsonResponse
    {
        return $this->created(new OfficeResource($this->offices->create($request->validated())));
    }

    /** Show office */
    public function show(Office $office): JsonResponse
    {
        return $this->ok(new OfficeResource($office->load('translations')));
    }

    /** Update office */
    public function update(UpdateOfficeRequest $request, Office $office): JsonResponse
    {
        return $this->ok(new OfficeResource($this->offices->update($office, $request->validated())));
    }

    /** Delete office */
    public function destroy(Office $office): JsonResponse
    {
        $this->offices->delete($office);

        return $this->deleted();
    }
}
