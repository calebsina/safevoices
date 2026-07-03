<?php

namespace App\Services\Audit;

use App\Enums\ActorType;
use App\Models\Audit\AuditLog;
use Illuminate\Database\Eloquent\Model;

/**
 * Single entry point for the append-only audit trail.
 *
 * Every sensitive action - evidence views/downloads, assignments,
 * status changes, user management - MUST flow through here. IPs are
 * hashed before storage so the log itself cannot deanonymise anyone.
 */
class AuditLogger
{
    public static function log(
        string $action,
        ?Model $auditable = null,
        ?string $description = null,
        array $metadata = [],
        ActorType $actorType = ActorType::User,
    ): AuditLog {
        $request = request();
        $user = auth('api')->user();

        // A missing staff user means the actor is a reporter or the system.
        if (! $user && $actorType === ActorType::User) {
            $actorType = ActorType::System;
        }

        return AuditLog::create([
            'actor_type'     => $actorType,
            'user_id'        => $user?->id,
            'action'         => $action,
            'auditable_type' => $auditable ? $auditable->getTable() : null,
            'auditable_id'   => $auditable ? (string) $auditable->getKey() : null,
            'description'    => $description,
            'ip_hash'        => $request?->ip() ? hash('sha256', $request->ip()) : null,
            'user_agent'     => $request ? substr((string) $request->userAgent(), 0, 255) : null,
            'metadata'       => $metadata ?: null,
        ]);
    }
}
