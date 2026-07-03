<?php

namespace App\Models\Reference;

use App\Traits\Translatable;
use Illuminate\Database\Eloquent\Model;

/** Lookup list `affected_person_types` (base + _translations, per Appendix 2 section 5). */
class AffectedPersonType extends Model
{
    use Translatable;

    protected $table = 'affected_person_types';

    protected $fillable = ['key', 'sort_order', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }
}
