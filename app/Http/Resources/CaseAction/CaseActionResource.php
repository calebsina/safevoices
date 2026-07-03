<?php

namespace App\Http\Resources\CaseAction;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CaseActionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'action_type' => $this->action_type,
            'notes'       => $this->notes,
            'user'        => $this->whenLoaded('user', fn () => $this->user?->name),
            'created_at'  => $this->created_at?->toIso8601String(),
        ];
    }
}
