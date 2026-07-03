<?php

namespace App\Models\Setting;

use Illuminate\Database\Eloquent\Model;

class SettingTranslation extends Model
{
    public $timestamps = false;

    protected $fillable = ['locale', 'value'];
}
