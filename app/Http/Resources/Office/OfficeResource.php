<?php

namespace App\Http\Resources\Office;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OfficeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'        => $this->id,
            'key'       => $this->key,
            'name'      => $this->t('name'), // current-locale label with fallback
            'region'    => $this->region,
            'is_active' => $this->is_active,
            // Full translation map for admin edit screens only.
            'translations' => $this->when(
                $request->boolean('with_translations'),
                fn () => $this->translations->keyBy('locale')
            ),
        ];
    }
}
