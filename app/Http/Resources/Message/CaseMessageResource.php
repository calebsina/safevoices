<?php

namespace App\Http\Resources\Message;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CaseMessageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'sender_type' => $this->sender_type,
            // Staff name is visible to staff; the reporter side only ever
            // sees sender_type (the reporter resource strips this).
            'sender_name' => $this->whenLoaded('sender', fn () => $this->sender?->name),
            'body'        => $this->body,
            'locale'      => $this->locale,
            'is_read'     => $this->is_read,
            'created_at'  => $this->created_at?->toIso8601String(),
        ];
    }
}
