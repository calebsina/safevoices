<?php

namespace App\Http\Resources\Reference;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CaseStatusResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'key'            => $this->key,
            'label'          => $this->t('label'),
            'reporter_label' => $this->t('reporter_label'),
            'sort_order'     => $this->sort_order,
            'is_terminal'    => $this->is_terminal,
            'color'          => $this->color,
            'translations'   => $this->when(
                $request->boolean('with_translations'),
                fn () => $this->translations->keyBy('locale')
            ),
        ];
    }
}
