<?php

namespace App\Http\Resources\Cms;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FaqResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'       => $this->id,
            'category' => $this->category,
            'question' => $this->t('question'),
            'answer'   => $this->t('answer'),
            'sort_order' => $this->sort_order,
            'is_active'  => $this->is_active,
            'translations' => $this->when(
                $request->boolean('with_translations'),
                fn () => $this->translations->keyBy('locale')
            ),
        ];
    }
}
