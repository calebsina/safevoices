<?php

namespace App\Services\Role;

use App\Models\Role\Role;
use App\Services\Audit\AuditLogger;
use App\Services\TranslatableCrudService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;

class RoleService extends TranslatableCrudService
{
    protected string $model = Role::class;

    protected array $with = ['translations', 'permissions'];

    public function create(array $data): Model
    {
        $permissions = $data['permissions'] ?? [];
        unset($data['permissions']);

        return $this->transaction(function () use ($data, $permissions) {
            $role = parent::create($data);
            $role->permissions()->sync($permissions);
            AuditLogger::log('role.created', $role);

            return $role->load($this->with);
        });
    }

    public function update(Model|string|int $model, array $data): Model
    {
        $permissions = $data['permissions'] ?? null;
        unset($data['permissions']);

        return $this->transaction(function () use ($model, $data, $permissions) {
            $role = parent::update($model, $data);

            if ($permissions !== null) {
                $role->permissions()->sync($permissions);
            }

            AuditLogger::log('role.updated', $role);

            return $role->load($this->with);
        });
    }

    public function delete(Model|string|int $model): bool
    {
        $role = $model instanceof Role ? $model : $this->find($model);

        // Core roles are protected by is_system (data dictionary section 4).
        if ($role->is_system) {
            throw ValidationException::withMessages(['role' => __('messages.role.system_protected')]);
        }

        AuditLogger::log('role.deleted', $role);

        return parent::delete($role);
    }

    protected function flushCaches(): void
    {
        // Permission keys are cached per role for the RBAC middleware.
        Role::pluck('id')->each(fn ($id) => Cache::forget("sv.role.$id.permissions"));
    }
}
