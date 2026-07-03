<?php

namespace App\Http\Controllers\Api\V1\Setting;

use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\Setting\StoreUiStringRequest;
use App\Http\Resources\Setting\UiStringResource;
use App\Models\Setting\UiString;
use App\Services\Setting\UiStringService;
use Illuminate\Http\JsonResponse;

/**
 * @group CMS
 *
 * DB-managed microcopy (second i18n layer). The public map endpoint is
 * what the SPA and the bot load at boot for the active locale.
 */
class UiStringController extends BaseController
{
    public function __construct(private readonly UiStringService $strings) {}

    /**
     * Public key -> value map for the current locale
     *
     * @unauthenticated
     * @response 200 {"success":true,"data":{"portal.followup.title":"Suivre mon signalement"}}
     */
    public function map(): JsonResponse
    {
        return $this->ok($this->strings->forLocale(app()->getLocale()));
    }

    /**
     * List UI strings (admin)
     *
     * @authenticated
     */
    public function index(): JsonResponse
    {
        return $this->ok(UiStringResource::collection($this->strings->query()->orderBy('group')->get()));
    }

    /**
     * Create UI string
     *
     * @authenticated
     */
    public function store(StoreUiStringRequest $request): JsonResponse
    {
        return $this->created(new UiStringResource($this->strings->create($request->validated())));
    }

    /**
     * Update UI string
     *
     * @authenticated
     */
    public function update(StoreUiStringRequest $request, UiString $ui_string): JsonResponse
    {
        return $this->ok(new UiStringResource($this->strings->update($ui_string, $request->validated())));
    }
}
