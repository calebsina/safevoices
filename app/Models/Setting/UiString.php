<?php

namespace App\Models\Setting;

use App\Traits\Translatable;
use Illuminate\Database\Eloquent\Model;

/**
 * DB-managed microcopy the CMS can edit without a deploy
 * (portal.followup.title, bot.greeting, ...).
 *
 * Two-layer i18n: purely static developer strings stay in
 * lang/en.json + lang/fr.json (fast, cached); anything an admin must be
 * able to change lives here.
 */
class UiString extends Model
{
    use Translatable;

    protected $fillable = ['key', 'group'];
}
