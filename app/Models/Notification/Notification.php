<?php

namespace App\Models\Notification;

use App\Enums\NotificationStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Sent-notification log. Polymorphic notifiable: a staff User or a
 * ReporterIdentity. Reporter payloads never contain case content.
 */
class Notification extends Model
{
    use HasUuids;

    protected $fillable = [
        'notifiable_type', 'notifiable_id', 'template_id',
        'channel', 'payload', 'status', 'sent_at', 'read_at',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'status'  => NotificationStatus::class,
            'sent_at' => 'datetime',
            'read_at' => 'datetime',
        ];
    }

    public function notifiable(): MorphTo
    {
        return $this->morphTo();
    }
}
