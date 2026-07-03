<?php

namespace App\Http\Resources\Consent;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ConsentVersionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'version'        => $this->version,
            'effective_from' => $this->effective_from?->toIso8601String(),
            'is_active'      => $this->is_active,
            'body'           => $this->t('body'),
            'translations'   => $this->when(
                $request->boolean('with_translations'),
                fn () => $this->translations->keyBy('locale')
            ),
        ];
    }
}
