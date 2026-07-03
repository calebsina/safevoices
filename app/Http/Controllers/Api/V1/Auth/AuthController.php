<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\User\UserResource;
use App\Services\Auth\AuthService;
use Illuminate\Http\JsonResponse;

/**
 * @group Staff / Authentication
 *
 * Staff authentication (caseworkers, supervisors, administrators) via
 * email + password (+ optional TOTP) returning a JWT bearer token.
 * Reporters never authenticate here - see the Follow-up group.
 */
class AuthController extends BaseController
{
    public function __construct(private readonly AuthService $auth) {}

    /**
     * Login
     *
     * @unauthenticated
     * @bodyParam email string required Staff email. Example: admin@safevoice.cm
     * @bodyParam password string required Password. Example: ChangeMe-Please-1!
     * @bodyParam otp string 6-digit TOTP code, required when MFA is enabled. Example: 123456
     * @response 200 scenario="Success" {"success":true,"message":"OK","data":{"access_token":"eyJ...","token_type":"bearer","expires_in":3600,"user":{"id":"...","name":"Admin"}}}
     */
    public function login(LoginRequest $request): JsonResponse
    {
        [$user, $token] = $this->auth->login(
            $request->validated('email'),
            $request->validated('password'),
            $request->validated('otp'),
        );

        return $this->ok(
            $this->auth->tokenPayload($token) + ['user' => new UserResource($user->load('role.translations', 'office.translations'))]
        );
    }

    /**
     * Current user
     *
     * @authenticated
     */
    public function me(): JsonResponse
    {
        return $this->ok(new UserResource(auth('api')->user()->load('role.translations', 'office.translations')));
    }

    /**
     * Refresh token
     *
     * @authenticated
     */
    public function refresh(): JsonResponse
    {
        return $this->ok($this->auth->tokenPayload($this->auth->refresh()));
    }

    /**
     * Logout (invalidate token)
     *
     * @authenticated
     */
    public function logout(): JsonResponse
    {
        $this->auth->logout();

        return $this->ok();
    }
}
