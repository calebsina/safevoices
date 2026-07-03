<?php

namespace App\Http\Controllers\Api\V1\Reference;

use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\Reference\StoreCaseCategoryRequest;
use App\Http\Requests\Reference\UpdateCaseCategoryRequest;
use App\Http\Resources\Reference\CaseCategoryResource;
use App\Models\Reference\CaseCategory;
use App\Services\Reference\CaseCategoryService;
use Illuminate\Http\JsonResponse;

/**
 * @group Reference data
 *
 * Case categories. Public read (the intake UI needs them); admin write
 * behind permission `reference.manage`.
 */
class CaseCategoryController extends BaseController
{
    public function __construct(private readonly CaseCategoryService $categories) {}

    /**
     * List categories (localized)
     *
     * @unauthenticated
     */
    public function index(): JsonResponse
    {
        return $this->ok(CaseCategoryResource::collection(
            $this->categories->query()
                ->whereNull('parent_id')
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get()
        ));
    }

    /**
     * Create category
     *
     * @authenticated
     */
    public function store(StoreCaseCategoryRequest $request): JsonResponse
    {
        return $this->created(new CaseCategoryResource($this->categories->create($request->validated())));
    }

    /**
     * Update category
     *
     * @authenticated
     */
    public function update(UpdateCaseCategoryRequest $request, CaseCategory $category): JsonResponse
    {
        return $this->ok(new CaseCategoryResource($this->categories->update($category, $request->validated())));
    }

    /**
     * Delete category
     *
     * @authenticated
     */
    public function destroy(CaseCategory $category): JsonResponse
    {
        $this->categories->delete($category);

        return $this->deleted();
    }
}
