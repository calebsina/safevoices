<?php

namespace App\Models\Cms;

use Illuminate\Database\Eloquent\Model;

class FaqTranslation extends Model
{
    public $timestamps = false;

    protected $fillable = ['locale', 'question', 'answer'];
}
