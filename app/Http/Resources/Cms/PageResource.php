<?php

namespace App\Http\Resources\Cms;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'       => $this->id,
            'slug'     => $this->slug,
            'key'      => $this->key,
            'template' => $this->template,
            'status'   => $this->status,
            'title'            => $this->t('title'),
            'localized_slug'   => $this->t('localized_slug'),
            'body'             => $this->t('body'),
            'meta_title'       => $this->t('meta_title'),
            'meta_description' => $this->t('meta_description'),
            'blocks' => $this->whenLoaded('blocks', fn () => $this->blocks
                ->where('is_active', true)
                ->values()
                ->map(fn ($block) => [
                    'key'        => $block->key,
                    'type'       => $block->type,
                    'settings'   => $block->settings,
                    'heading'    => $block->t('heading'),
                    'subheading' => $block->t('subheading'),
                    'body'       => $block->t('body'),
                    'cta_label'  => $block->t('cta_label'),
                    'cta_url'    => $block->t('cta_url'),
                    'extra'      => $block->t('extra'),
                ])),
            'translations' => $this->when(
                $request->boolean('with_translations'),
                fn () => $this->translations->keyBy('locale')
            ),
        ];
    }
}
