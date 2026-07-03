<?php

namespace App\Services\Office;

use App\Models\Office\Office;
use App\Services\TranslatableCrudService;

class OfficeService extends TranslatableCrudService
{
    protected string $model = Office::class;
}
