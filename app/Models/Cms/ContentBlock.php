<?php

namespace App\Models\Cms;

use App\Traits\Translatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Section within a page (hero, steps, cta, stats). page_id NULL means
 * a global / reusable block. `settings` (jsonb) holds non-text config;
 * translations hold every visible string.
 */
class ContentBlock extends Model
{
    use Translatable;

    protected $fillable = ['page_id', 'key', 'type', 'settings', 'sort_order', 'is_active'];

    protected function casts(): array
    {
        return [
            'settings'  => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }
}
