<?php

namespace App\Http\Resources\Reference;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** Shared shape for the four small lookup lists. */
class LookupResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'key'         => $this->key,
            'label'       => $this->t('label'),
            'description' => $this->t('description'),
            'sort_order'  => $this->sort_order,
            'is_active'   => $this->is_active,
            'translations' => $this->when(
                $request->boolean('with_translations'),
                fn () => $this->translations->keyBy('locale')
            ),
        ];
    }
}
