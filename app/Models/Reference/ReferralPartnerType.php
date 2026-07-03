<?php

namespace App\Models\Reference;

use App\Traits\Translatable;
use Illuminate\Database\Eloquent\Model;

/** Lookup list `referral_partner_types` (base + _translations, per Appendix 2 section 5). */
class ReferralPartnerType extends Model
{
    use Translatable;

    protected $table = 'referral_partner_types';

    protected $fillable = ['key', 'sort_order', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }
}
