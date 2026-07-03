<?php

namespace App\Http\Controllers\Api\V1\User;

use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Resources\User\UserResource;
use App\Models\User\User;
use App\Services\User\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @group Admin / Users
 *
 * Staff account management. Requires permission `users.manage`.
 * Accounts are deactivated, never hard-deleted (audit integrity).
 *
 * @authenticated
 */
class UserController extends BaseController
{
    public function __construct(private readonly UserService $users) {}

    /**
     * List staff users
     *
     * @queryParam role_id integer Example: 1
     * @queryParam office_id integer Example: 1
     * @queryParam is_active boolean Example: true
     * @queryParam search string Name/email search. Example: aiche
     */
    public function index(Request $request): JsonResponse
    {
        return $this->paginated(UserResource::collection(
            $this->users->search($request->query(), (int) $request->query('per_page', 15))
        ));
    }

    /** Create staff user */
    public function store(StoreUserRequest $request): JsonResponse
    {
        return $this->created(new UserResource($this->users->create($request->validated())));
    }

    /** Show staff user */
    public function show(User $user): JsonResponse
    {
        return $this->ok(new UserResource($user->load('role.translations', 'office.translations')));
    }

    /** Update staff user */
    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        return $this->ok(new UserResource($this->users->update($user, $request->validated())));
    }

    /** Deactivate staff user */
    public function destroy(User $user): JsonResponse
    {
        $this->users->deactivate($user);

        return $this->deleted();
    }
}
