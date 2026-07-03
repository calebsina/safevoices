<?php

namespace App\Http\Resources\Setting;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SettingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'key'             => $this->key,
            'group'           => $this->group,
            'type'            => $this->type,
            'is_translatable' => $this->is_translatable,
            'value'           => $this->resolvedValue(),
            'translations'    => $this->when(
                $request->boolean('with_translations'),
                fn () => $this->translations->keyBy('locale')
            ),
        ];
    }
}
