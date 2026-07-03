<?php

namespace App\Http\Resources\Reference;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PriorityLevelResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'key'         => $this->key,
            'label'       => $this->t('label'),
            'score_min'   => $this->score_min,
            'score_max'   => $this->score_max,
            'sla_minutes' => $this->sla_minutes,
            'color'       => $this->color,
            'sort_order'  => $this->sort_order,
        ];
    }
}
