<?php

namespace App\Http\Resources\Audit;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuditLogResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'actor_type'     => $this->actor_type,
            'user'           => $this->whenLoaded('user', fn () => $this->user?->only(['id', 'name', 'email'])),
            'action'         => $this->action,
            'auditable_type' => $this->auditable_type,
            'auditable_id'   => $this->auditable_id,
            'description'    => $this->description,
            'metadata'       => $this->metadata,
            'created_at'     => $this->created_at?->toIso8601String(),
        ];
    }
}
