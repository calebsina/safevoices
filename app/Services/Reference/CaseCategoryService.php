<?php

namespace App\Services\Reference;

use App\Models\Reference\CaseCategory;
use App\Services\TranslatableCrudService;

class CaseCategoryService extends TranslatableCrudService
{
    protected string $model = CaseCategory::class;

    protected array $with = ['translations', 'children.translations'];
}
