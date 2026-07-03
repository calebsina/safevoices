<?php

namespace App\Models\Office;

use Illuminate\Database\Eloquent\Model;

class OfficeTranslation extends Model
{
    public $timestamps = false;

    protected $fillable = ['locale', 'name'];
}
