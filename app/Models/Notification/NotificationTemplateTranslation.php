<?php

namespace App\Models\Notification;

use Illuminate\Database\Eloquent\Model;

class NotificationTemplateTranslation extends Model
{
    public $timestamps = false;

    protected $fillable = ['locale', 'subject', 'body'];
}
