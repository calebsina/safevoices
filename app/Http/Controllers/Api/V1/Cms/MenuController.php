<?php

namespace App\Http\Controllers\Api\V1\Cms;

use App\Http\Controllers\Api\BaseController;
use App\Http\Resources\Cms\MenuResource;
use App\Services\Cms\MenuService;
use Illuminate\Http\JsonResponse;

/**
 * @group CMS
 */
class MenuController extends BaseController
{
    public function __construct(private readonly MenuService $menus) {}

    /**
     * Public menu by key (localized)
     *
     * @unauthenticated
     * @urlParam key string required Example: header
     */
    public function show(string $key): JsonResponse
    {
        return $this->ok(new MenuResource($this->menus->byKey($key)));
    }
}
