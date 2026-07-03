<?php

namespace App\Http\Controllers\Api\V1\Notification;

use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\Notification\StoreTemplateRequest;
use App\Http\Resources\Notification\NotificationTemplateResource;
use App\Models\Notification\NotificationTemplate;
use App\Services\Notification\NotificationTemplateService;
use Illuminate\Http\JsonResponse;

/**
 * @group Admin / Notification templates
 *
 * Reporter-facing template bodies must stay discreet: reference code
 * and status wording only, never case content.
 *
 * @authenticated
 */
class NotificationTemplateController extends BaseController
{
    public function __construct(private readonly NotificationTemplateService $templates) {}

    /** List templates */
    public function index(): JsonResponse
    {
        return $this->ok(NotificationTemplateResource::collection($this->templates->query()->get()));
    }

    /** Create template */
    public function store(StoreTemplateRequest $request): JsonResponse
    {
        return $this->created(new NotificationTemplateResource($this->templates->create($request->validated())));
    }

    /** Update template */
    public function update(StoreTemplateRequest $request, NotificationTemplate $template): JsonResponse
    {
        return $this->ok(new NotificationTemplateResource($this->templates->update($template, $request->validated())));
    }
}
