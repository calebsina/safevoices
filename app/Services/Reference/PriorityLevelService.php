<?php

namespace App\Services\Reference;

use App\Models\Reference\PriorityLevel;
use App\Services\TranslatableCrudService;

class PriorityLevelService extends TranslatableCrudService
{
    protected string $model = PriorityLevel::class;
}
