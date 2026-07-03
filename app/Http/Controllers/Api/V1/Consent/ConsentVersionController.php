<?php

namespace App\Http\Controllers\Api\V1\Consent;

use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\Consent\StoreConsentVersionRequest;
use App\Http\Resources\Consent\ConsentVersionResource;
use App\Models\Consent\ConsentVersion;
use App\Services\Cms\ConsentVersionService;
use Illuminate\Http\JsonResponse;

/**
 * @group Consent
 */
class ConsentVersionController extends BaseController
{
    public function __construct(private readonly ConsentVersionService $versions) {}

    /**
     * Active consent text (public - shown at intake step 2)
     *
     * @unauthenticated
     */
    public function active(): JsonResponse
    {
        $version = ConsentVersion::active()?->load('translations');

        return $version
            ? $this->ok(new ConsentVersionResource($version))
            : $this->fail(__('messages.intake.no_consent_version'), 404);
    }

    /**
     * List versions (admin)
     *
     * @authenticated
     */
    public function index(): JsonResponse
    {
        return $this->ok(ConsentVersionResource::collection($this->versions->query()->latest('effective_from')->get()));
    }

    /**
     * Create version
     *
     * @authenticated
     */
    public function store(StoreConsentVersionRequest $request): JsonResponse
    {
        return $this->created(new ConsentVersionResource($this->versions->create($request->validated())));
    }
}
