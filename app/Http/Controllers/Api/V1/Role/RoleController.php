<?php

namespace App\Http\Controllers\Api\V1\Role;

use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\Role\StoreRoleRequest;
use App\Http\Requests\Role\UpdateRoleRequest;
use App\Http\Resources\Role\RoleResource;
use App\Models\Role\Permission;
use App\Models\Role\Role;
use App\Services\Role\RoleService;
use Illuminate\Http\JsonResponse;

/**
 * @group Admin / Roles & permissions
 *
 * Requires permission `roles.manage`. System roles (caseworker,
 * supervisor, administrator) cannot be deleted.
 *
 * @authenticated
 */
class RoleController extends BaseController
{
    public function __construct(private readonly RoleService $roles) {}

    /** List roles */
    public function index(): JsonResponse
    {
        return $this->ok(RoleResource::collection($this->roles->query()->get()));
    }

    /** All permission keys (for the role editor) */
    public function permissions(): JsonResponse
    {
        return $this->ok(Permission::orderBy('group')->orderBy('key')->get(['id', 'key', 'group']));
    }

    /** Create role */
    public function store(StoreRoleRequest $request): JsonResponse
    {
        return $this->created(new RoleResource($this->roles->create($request->validated())));
    }

    /** Show role */
    public function show(Role $role): JsonResponse
    {
        return $this->ok(new RoleResource($role->load('translations', 'permissions')));
    }

    /** Update role */
    public function update(UpdateRoleRequest $request, Role $role): JsonResponse
    {
        return $this->ok(new RoleResource($this->roles->update($role, $request->validated())));
    }

    /** Delete role (non-system only) */
    public function destroy(Role $role): JsonResponse
    {
        $this->roles->delete($role);

        return $this->deleted();
    }
}
