<?php

namespace App\Http\Resources\Reference;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CaseCategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'key'             => $this->key,
            'name'            => $this->t('name'),
            'description'     => $this->t('description'),
            'parent_id'       => $this->parent_id,
            'severity_weight' => $this->severity_weight,
            'sort_order'      => $this->sort_order,
            'is_active'       => $this->is_active,
            'children'        => self::collection($this->whenLoaded('children')),
            'translations'    => $this->when(
                $request->boolean('with_translations'),
                fn () => $this->translations->keyBy('locale')
            ),
        ];
    }
}
