<?php

namespace App\Services\Audit;

use App\Models\Audit\AuditLog;
use App\Services\BaseService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/** Read-side of the audit trail (admin screens). */
class AuditLogService extends BaseService
{
    protected string $model = AuditLog::class;

    protected array $with = ['user:id,name,email'];

    public function search(array $filters, int $perPage = 25): LengthAwarePaginator
    {
        return $this->query()
            ->when($filters['action'] ?? null, fn ($q, $v) => $q->where('action', 'like', "$v%"))
            ->when($filters['user_id'] ?? null, fn ($q, $v) => $q->where('user_id', $v))
            ->when($filters['auditable_type'] ?? null, fn ($q, $v) => $q->where('auditable_type', $v))
            ->when($filters['auditable_id'] ?? null, fn ($q, $v) => $q->where('auditable_id', $v))
            ->when($filters['from'] ?? null, fn ($q, $v) => $q->where('created_at', '>=', $v))
            ->when($filters['to'] ?? null, fn ($q, $v) => $q->where('created_at', '<=', $v))
            ->latest('created_at')
            ->paginate(min($perPage, 100));
    }
}
