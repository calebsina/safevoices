<?php

namespace App\Services\Notification;

use App\Models\Notification\NotificationTemplate;
use App\Services\TranslatableCrudService;

class NotificationTemplateService extends TranslatableCrudService
{
    protected string $model = NotificationTemplate::class;
}
