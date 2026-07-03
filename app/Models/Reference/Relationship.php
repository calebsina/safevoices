<?php

namespace App\Models\Reference;

use App\Traits\Translatable;
use Illuminate\Database\Eloquent\Model;

/** Lookup list `relationships` (base + _translations, per Appendix 2 section 5). */
class Relationship extends Model
{
    use Translatable;

    protected $table = 'relationships';

    protected $fillable = ['key', 'sort_order', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }
}
