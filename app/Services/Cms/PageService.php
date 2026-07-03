<?php

namespace App\Services\Cms;

use App\Models\Cms\Page;
use App\Services\TranslatableCrudService;

class PageService extends TranslatableCrudService
{
    protected string $model = Page::class;

    protected array $with = ['translations', 'blocks.translations'];

    /** Public page resolution by slug or localized slug. */
    public function publicBySlug(string $slug): Page
    {
        return $this->query()
            ->published()
            ->where(function ($q) use ($slug) {
                $q->where('slug', $slug)
                  ->orWhereHas('translations', fn ($t) => $t->where('localized_slug', $slug));
            })
            ->firstOrFail();
    }
}
