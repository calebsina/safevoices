<?php

namespace App\Http\Controllers\Api\V1\Reference;

use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\Reference\LookupRequest;
use App\Http\Resources\Reference\LookupResource;
use App\Services\Reference\LookupService;
use Illuminate\Http\JsonResponse;

/**
 * @group Reference data
 *
 * One endpoint family for the four small lookup lists. `{type}` is one
 * of: affected-person-types, relationships, referral-partner-types,
 * channels.
 */
class LookupController extends BaseController
{
    public function __construct(private readonly LookupService $lookups) {}

    /**
     * List a lookup type (localized)
     *
     * @unauthenticated
     * @urlParam type string required Example: relationships
     */
    public function index(string $type): JsonResponse
    {
        return $this->ok(LookupResource::collection(
            $this->lookups->forType($type)->activeOrdered()->get()
        ));
    }

    /**
     * Create entry
     *
     * @authenticated
     */
    public function store(LookupRequest $request, string $type): JsonResponse
    {
        return $this->created(new LookupResource(
            $this->lookups->forType($type)->create($request->validated())
        ));
    }

    /**
     * Update entry
     *
     * @authenticated
     */
    public function update(LookupRequest $request, string $type, int $id): JsonResponse
    {
        return $this->ok(new LookupResource(
            $this->lookups->forType($type)->update($id, $request->validated())
        ));
    }

    /**
     * Delete entry
     *
     * @authenticated
     */
    public function destroy(string $type, int $id): JsonResponse
    {
        $this->lookups->forType($type)->delete($id);

        return $this->deleted();
    }
}
