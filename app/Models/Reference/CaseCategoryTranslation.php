<?php

namespace App\Models\Reference;

use Illuminate\Database\Eloquent\Model;

class CaseCategoryTranslation extends Model
{
    public $timestamps = false;

    protected $fillable = ['locale', 'name', 'description'];
}
