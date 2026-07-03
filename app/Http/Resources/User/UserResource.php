<?php

namespace App\Http\Resources\User;

use App\Http\Resources\Office\OfficeResource;
use App\Http\Resources\Role\RoleResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** Staff account. Never leaks password/mfa_secret (also model-hidden). */
class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'name'          => $this->name,
            'email'         => $this->email,
            'phone'         => $this->phone,
            'is_active'     => $this->is_active,
            'mfa_enabled'   => $this->mfa_enabled,
            'last_login_at' => $this->last_login_at?->toIso8601String(),
            'role'          => new RoleResource($this->whenLoaded('role')),
            'office'        => new OfficeResource($this->whenLoaded('office')),
            'created_at'    => $this->created_at?->toIso8601String(),
        ];
    }
}
