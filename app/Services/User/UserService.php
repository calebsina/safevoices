<?php

namespace App\Services\User;

use App\Models\User\User;
use App\Services\Audit\AuditLogger;
use App\Services\BaseService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;

/** Staff account management (administrators only). */
class UserService extends BaseService
{
    protected string $model = User::class;

    protected array $with = ['role.translations', 'office.translations'];

    public function search(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        return $this->query()
            ->when($filters['role_id'] ?? null, fn ($q, $v) => $q->where('role_id', $v))
            ->when($filters['office_id'] ?? null, fn ($q, $v) => $q->where('office_id', $v))
            ->when(isset($filters['is_active']), fn ($q) => $q->where('is_active', filter_var($filters['is_active'], FILTER_VALIDATE_BOOLEAN)))
            ->when($filters['search'] ?? null, fn ($q, $v) => $q->where(
                fn ($qq) => $qq->where('name', 'ilike', "%$v%")->orWhere('email', 'ilike', "%$v%")
            ))
            ->orderBy('name')
            ->paginate(min($perPage, 100));
    }

    public function create(array $data): Model
    {
        return $this->transaction(function () use ($data) {
            $user = User::create($data);
            AuditLogger::log('user.created', $user, "Created staff user {$user->email}");

            return $user->load($this->with);
        });
    }

    public function update(Model|string|int $model, array $data): Model
    {
        $user = $model instanceof Model ? $model : $this->find($model);

        return $this->transaction(function () use ($user, $data) {
            // Never silently overwrite a password with an empty value.
            if (($data['password'] ?? null) === null) {
                unset($data['password']);
            }

            $user->update($data);
            AuditLogger::log('user.updated', $user);

            return $user->refresh()->load($this->with);
        });
    }

    /** Deactivate instead of delete (data dictionary: users.is_active). */
    public function deactivate(User $user): User
    {
        $user->update(['is_active' => false]);
        AuditLogger::log('user.deactivated', $user);

        return $user;
    }
}
