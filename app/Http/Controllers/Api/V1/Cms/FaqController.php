<?php

namespace App\Http\Controllers\Api\V1\Cms;

use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\Cms\StoreFaqRequest;
use App\Http\Resources\Cms\FaqResource;
use App\Models\Cms\Faq;
use App\Services\Cms\FaqService;
use Illuminate\Http\JsonResponse;

/**
 * @group CMS
 */
class FaqController extends BaseController
{
    public function __construct(private readonly FaqService $faqs) {}

    /**
     * Public FAQ list (localized)
     *
     * @unauthenticated
     */
    public function index(): JsonResponse
    {
        return $this->ok(FaqResource::collection($this->faqs->publicList()));
    }

    /**
     * Create FAQ
     *
     * @authenticated
     */
    public function store(StoreFaqRequest $request): JsonResponse
    {
        return $this->created(new FaqResource($this->faqs->create($request->validated())));
    }

    /**
     * Update FAQ
     *
     * @authenticated
     */
    public function update(StoreFaqRequest $request, Faq $faq): JsonResponse
    {
        return $this->ok(new FaqResource($this->faqs->update($faq, $request->validated())));
    }

    /**
     * Delete FAQ
     *
     * @authenticated
     */
    public function destroy(Faq $faq): JsonResponse
    {
        $this->faqs->delete($faq);

        return $this->deleted();
    }
}
