<?php

namespace App\Models\Cms;

use App\Traits\Translatable;
use Illuminate\Database\Eloquent\Model;

class Faq extends Model
{
    use Translatable;

    protected $fillable = ['category', 'sort_order', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }
}
