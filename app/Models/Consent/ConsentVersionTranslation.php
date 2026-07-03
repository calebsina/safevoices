<?php

namespace App\Models\Consent;

use Illuminate\Database\Eloquent\Model;

class ConsentVersionTranslation extends Model
{
    public $timestamps = false;

    protected $fillable = ['locale', 'body'];
}
