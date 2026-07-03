<?php

namespace App\Services\Cms;

use App\Models\Cms\Faq;
use App\Services\TranslatableCrudService;

class FaqService extends TranslatableCrudService
{
    protected string $model = Faq::class;

    public function publicList()
    {
        return $this->query()->where('is_active', true)->orderBy('sort_order')->get();
    }
}
