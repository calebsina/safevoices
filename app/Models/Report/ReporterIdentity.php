<?php

namespace App\Models\Report;

use App\Models\Reference\Channel;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * The tokenised reporter - the ONLY link to a real phone number.
 *
 * - phone_hash:      SHA-256 of the E.164 number, used exclusively for
 *                    de-duplication; irreversible.
 * - phone_encrypted: reversible only by the messaging layer to deliver
 *                    WhatsApp/SMS; NULL when contact was declined.
 *
 * This model is NEVER exposed through any staff-facing resource:
 * caseworkers only ever see the case reference code.
 */
class ReporterIdentity extends Model
{
    use HasUuids;

    protected $fillable = [
        'channel_id', 'phone_hash', 'phone_encrypted', 'locale',
        'contact_consent', 'first_seen_at', 'last_seen_at',
    ];

    protected $hidden = ['phone_hash', 'phone_encrypted'];

    protected function casts(): array
    {
        return [
            'phone_encrypted' => 'encrypted', // (lock) app-layer encryption
            'contact_consent' => 'boolean',
            'first_seen_at'   => 'datetime',
            'last_seen_at'    => 'datetime',
        ];
    }

    public function channel(): BelongsTo
    {
        return $this->belongsTo(Channel::class);
    }

    public function reports(): HasMany
    {
        return $this->hasMany(Report::class);
    }

    /** One-way hash used to find a returning reporter without storing the number. */
    public static function hashPhone(string $e164): string
    {
        return hash('sha256', $e164);
    }
}
