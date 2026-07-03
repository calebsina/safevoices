<?php

namespace App\Http\Resources\Role;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RoleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'key'         => $this->key,
            'label'       => $this->t('label'),
            'description' => $this->t('description'),
            'is_system'   => $this->is_system,
            'permissions' => $this->whenLoaded('permissions', fn () => $this->permissions->pluck('key')),
            'translations' => $this->when(
                $request->boolean('with_translations'),
                fn () => $this->translations->keyBy('locale')
            ),
        ];
    }
}
