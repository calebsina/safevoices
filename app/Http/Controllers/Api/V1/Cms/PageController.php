<?php

namespace App\Http\Controllers\Api\V1\Cms;

use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\Cms\StorePageRequest;
use App\Http\Requests\Cms\UpdatePageRequest;
use App\Http\Resources\Cms\PageResource;
use App\Models\Cms\Page;
use App\Services\Cms\PageService;
use Illuminate\Http\JsonResponse;

/**
 * @group CMS
 *
 * Dynamic pages. Public resolution by slug (or localized slug) serves
 * the landing page / portal; admin CRUD behind permission `cms.manage`.
 */
class PageController extends BaseController
{
    public function __construct(private readonly PageService $pages) {}

    /**
     * Public page by slug
     *
     * @unauthenticated
     * @urlParam slug string required Example: how-it-works
     */
    public function showPublic(string $slug): JsonResponse
    {
        return $this->ok(new PageResource($this->pages->publicBySlug($slug)));
    }

    /**
     * List pages (admin)
     *
     * @authenticated
     */
    public function index(): JsonResponse
    {
        return $this->paginated(PageResource::collection($this->pages->paginate(25)));
    }

    /**
     * Create page
     *
     * @authenticated
     */
    public function store(StorePageRequest $request): JsonResponse
    {
        return $this->created(new PageResource($this->pages->create($request->validated())));
    }

    /**
     * Show page (admin)
     *
     * @authenticated
     */
    public function show(Page $page): JsonResponse
    {
        return $this->ok(new PageResource($page->load('translations', 'blocks.translations')));
    }

    /**
     * Update page
     *
     * @authenticated
     */
    public function update(UpdatePageRequest $request, Page $page): JsonResponse
    {
        return $this->ok(new PageResource($this->pages->update($page, $request->validated())));
    }

    /**
     * Delete page
     *
     * @authenticated
     */
    public function destroy(Page $page): JsonResponse
    {
        $this->pages->delete($page);

        return $this->deleted();
    }
}
