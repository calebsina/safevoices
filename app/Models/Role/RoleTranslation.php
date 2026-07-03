<?php

namespace App\Models\Role;

use Illuminate\Database\Eloquent\Model;

class RoleTranslation extends Model
{
    public $timestamps = false;

    protected $fillable = ['locale', 'label', 'description'];
}
