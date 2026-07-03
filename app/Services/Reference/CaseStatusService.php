<?php

namespace App\Services\Reference;

use App\Models\Reference\CaseStatus;
use App\Services\TranslatableCrudService;

class CaseStatusService extends TranslatableCrudService
{
    protected string $model = CaseStatus::class;
}
