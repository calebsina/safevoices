<?php

namespace App\Models\Cms;

use Illuminate\Database\Eloquent\Model;

class MediaAssetTranslation extends Model
{
    public $timestamps = false;

    protected $fillable = ['locale', 'alt_text', 'caption'];
}
