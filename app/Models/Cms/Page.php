<?php

namespace App\Models\Cms;

use App\Traits\Translatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * CMS page - every public string on the landing page / portal resolves
 * through the CMS layer so nothing is hard-coded in the front-end.
 */
class Page extends Model
{
    use SoftDeletes, Translatable;

    protected $fillable = [
        'slug', 'key', 'template', 'status', 'parent_id',
        'sort_order', 'is_system', 'published_at',
    ];

    protected function casts(): array
    {
        return [
            'is_system'    => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function blocks(): HasMany
    {
        return $this->hasMany(ContentBlock::class)->orderBy('sort_order');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }
}
