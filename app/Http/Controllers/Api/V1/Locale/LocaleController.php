<?php

namespace App\Http\Controllers\Api\V1\Locale;

use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\Locale\StoreLocaleRequest;
use App\Http\Requests\Locale\UpdateLocaleRequest;
use App\Http\Resources\Locale\LocaleResource;
use App\Models\Localization\Locale;
use App\Services\Locale\LocaleService;
use Illuminate\Http\JsonResponse;

/**
 * @group Localization
 *
 * Public listing of active locales; admin CRUD for enabling a new
 * language (adding a row is all it takes - no migration).
 */
class LocaleController extends BaseController
{
    public function __construct(private readonly LocaleService $locales) {}

    /**
     * Active locales
     *
     * @unauthenticated
     */
    public function index(): JsonResponse
    {
        return $this->ok(LocaleResource::collection(
            Locale::where('is_active', true)->orderBy('sort_order')->get()
        ));
    }

    /**
     * Create locale
     *
     * @authenticated
     */
    public function store(StoreLocaleRequest $request): JsonResponse
    {
        return $this->created(new LocaleResource($this->locales->create($request->validated())));
    }

    /**
     * Update locale
     *
     * @authenticated
     */
    public function update(UpdateLocaleRequest $request, Locale $locale): JsonResponse
    {
        return $this->ok(new LocaleResource($this->locales->update($locale, $request->validated())));
    }
}
