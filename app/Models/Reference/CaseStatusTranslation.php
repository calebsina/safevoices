<?php

namespace App\Models\Reference;

use Illuminate\Database\Eloquent\Model;

class CaseStatusTranslation extends Model
{
    public $timestamps = false;

    protected $fillable = ['locale', 'label', 'reporter_label', 'description'];
}
