<?php

namespace App\Http\Resources\Notification;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationTemplateResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                     => $this->id,
            'key'                    => $this->key,
            'channel'                => $this->channel,
            'whatsapp_template_name' => $this->whatsapp_template_name,
            'is_active'              => $this->is_active,
            'subject'                => $this->t('subject'),
            'body'                   => $this->t('body'),
            'translations'           => $this->when(
                $request->boolean('with_translations'),
                fn () => $this->translations->keyBy('locale')
            ),
        ];
    }
}
