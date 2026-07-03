<?php

namespace App\Models\Audit;

use App\Enums\ActorType;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Append-only audit trail. NEVER updated, NEVER soft-deleted.
 * Written exclusively through App\Services\Audit\AuditLogger.
 */
class AuditLog extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'actor_type', 'user_id', 'action', 'auditable_type', 'auditable_id',
        'description', 'ip_hash', 'user_agent', 'metadata',
    ];

    protected function casts(): array
    {
        return [
            'actor_type' => ActorType::class,
            'metadata'   => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
