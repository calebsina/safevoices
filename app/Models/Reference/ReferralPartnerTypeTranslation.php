<?php

namespace App\Models\Reference;

use Illuminate\Database\Eloquent\Model;

class ReferralPartnerTypeTranslation extends Model
{
    public $timestamps = false;

    protected $fillable = ['locale', 'label', 'description'];
}
