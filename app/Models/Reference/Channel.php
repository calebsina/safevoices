<?php

namespace App\Models\Reference;

use App\Traits\Translatable;
use Illuminate\Database\Eloquent\Model;

/** Lookup list `channels` (base + _translations, per Appendix 2 section 5). */
class Channel extends Model
{
    use Translatable;

    protected $table = 'channels';

    protected $fillable = ['key', 'sort_order', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }
}
