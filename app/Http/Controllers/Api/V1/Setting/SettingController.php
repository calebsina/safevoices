<?php

namespace App\Http\Controllers\Api\V1\Setting;

use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\Setting\UpdateSettingRequest;
use App\Http\Resources\Setting\SettingResource;
use App\Models\Setting\Setting;
use App\Services\Setting\SettingService;
use Illuminate\Http\JsonResponse;

/**
 * @group Admin / Settings
 */
class SettingController extends BaseController
{
    public function __construct(private readonly SettingService $settings) {}

    /**
     * Public settings (whitelisted keys only)
     *
     * @unauthenticated
     */
    public function publicIndex(): JsonResponse
    {
        return $this->ok($this->settings->publicSettings());
    }

    /**
     * List all settings
     *
     * @authenticated
     */
    public function index(): JsonResponse
    {
        return $this->ok(SettingResource::collection($this->settings->query()->orderBy('group')->get()));
    }

    /**
     * Update a setting
     *
     * @authenticated
     */
    public function update(UpdateSettingRequest $request, Setting $setting): JsonResponse
    {
        return $this->ok(new SettingResource($this->settings->update($setting, $request->validated())));
    }
}
