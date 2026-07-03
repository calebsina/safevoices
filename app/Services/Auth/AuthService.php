<?php

namespace App\Services\Auth;

use App\Models\User\User;
use App\Services\Audit\AuditLogger;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use PragmaRX\Google2FA\Google2FA;

/**
 * Staff authentication: email + password, optional TOTP MFA, JWT.
 *
 * Reporters never authenticate here - their per-case code + PIN flow
 * lives in the FollowUp module.
 */
class AuthService
{
    /**
     * Attempt login. Returns [user, token].
     *
     * @throws ValidationException on bad credentials / missing OTP
     */
    public function login(string $email, string $password, ?string $otp = null): array
    {
        /** @var User|null $user */
        $user = User::with('role')->where('email', $email)->first();

        if (! $user || ! Hash::check($password, $user->password)) {
            // Same message for unknown email and wrong password.
            throw ValidationException::withMessages(['email' => __('messages.auth.invalid_credentials')]);
        }

        if (! $user->is_active) {
            throw ValidationException::withMessages(['email' => __('messages.auth.account_disabled')]);
        }

        // MFA (dossier 4.4: MFA for staff). TOTP through pragmarx/google2fa.
        if ($user->mfa_enabled) {
            if ($otp === null || $otp === '') {
                throw ValidationException::withMessages(['otp' => __('messages.auth.otp_required')]);
            }

            if (! (new Google2FA)->verifyKey($user->mfa_secret, $otp)) {
                throw ValidationException::withMessages(['otp' => __('messages.auth.otp_invalid')]);
            }
        }

        $token = auth('api')->login($user);

        $user->forceFill(['last_login_at' => now()])->save();
        AuditLogger::log('auth.login', $user);

        return [$user, $token];
    }

    public function logout(): void
    {
        AuditLogger::log('auth.logout', auth('api')->user());
        auth('api')->logout(); // invalidates the current JWT
    }

    /** Issue a fresh token from a still-valid (or refreshable) one. */
    public function refresh(): string
    {
        try {
            return auth('api')->refresh();
        } catch (\Throwable) {
            throw new AuthenticationException;
        }
    }

    /** Standard token envelope shared by login/refresh responses. */
    public function tokenPayload(string $token): array
    {
        return [
            'access_token' => $token,
            'token_type'   => 'bearer',
            'expires_in'   => auth('api')->factory()->getTTL() * 60, // seconds
        ];
    }
}
