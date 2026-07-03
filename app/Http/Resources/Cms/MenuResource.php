<?php

namespace App\Http\Resources\Cms;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MenuResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'key'   => $this->key,
            'items' => $this->whenLoaded('items', fn () => $this->items->map(fn ($item) => $this->item($item))),
        ];
    }

    private function item($item): array
    {
        return [
            'label'    => $item->t('label'),
            'url'      => $item->url ?? ($item->page ? '/'.$item->page->slug : null),
            'children' => $item->children->map(fn ($child) => $this->item($child)),
        ];
    }
}
