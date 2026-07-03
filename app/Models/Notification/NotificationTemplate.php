<?php

namespace App\Models\Notification;

use App\Traits\Translatable;
use Illuminate\Database\Eloquent\Model;

/**
 * Outbound message template. WhatsApp entries carry the Meta-approved
 * template name; translations carry discreet, locale-specific wording
 * that must never expose case content (safety requirement).
 */
class NotificationTemplate extends Model
{
    use Translatable;

    protected $fillable = ['key', 'channel', 'whatsapp_template_name', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }
}
