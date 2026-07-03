<?php

namespace App\Models\Cms;

use Illuminate\Database\Eloquent\Model;

class MenuItemTranslation extends Model
{
    public $timestamps = false;

    protected $fillable = ['locale', 'label'];
}
