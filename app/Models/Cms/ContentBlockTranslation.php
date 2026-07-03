<?php

namespace App\Models\Cms;

use Illuminate\Database\Eloquent\Model;

class ContentBlockTranslation extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'locale', 'heading', 'subheading', 'body', 'cta_label', 'cta_url', 'extra',
    ];

    protected function casts(): array
    {
        return ['extra' => 'array'];
    }
}
