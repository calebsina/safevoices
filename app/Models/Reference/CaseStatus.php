<?php

namespace App\Models\Reference;

use App\Traits\Translatable;
use Illuminate\Database\Eloquent\Model;

/**
 * Case lifecycle state. Translations carry BOTH a staff `label` and a
 * simplified `reporter_label` (the wording shown through Amie / portal).
 */
class CaseStatus extends Model
{
    use Translatable;

    public const SUBMITTED    = 'submitted';
    public const TRIAGED      = 'triaged';
    public const ASSIGNED     = 'assigned';
    public const IN_PROGRESS  = 'in_progress';
    public const ACTION_TAKEN = 'action_taken';
    public const REFERRED     = 'referred';
    public const RESOLVED     = 'resolved';

    protected $fillable = ['key', 'sort_order', 'is_terminal', 'color'];

    protected function casts(): array
    {
        return ['is_terminal' => 'boolean'];
    }

    public static function byKey(string $key): self
    {
        return static::where('key', $key)->firstOrFail();
    }
}
