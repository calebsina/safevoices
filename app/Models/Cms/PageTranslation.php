<?php

namespace App\Models\Cms;

use Illuminate\Database\Eloquent\Model;

class PageTranslation extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'locale', 'title', 'localized_slug', 'body', 'meta_title', 'meta_description',
    ];
}
