<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\BaseFormRequest;

class LoginRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
            // TOTP code, required only when the account has MFA enabled
            // (enforced in AuthService so we don't leak MFA status here).
            'otp'      => ['nullable', 'digits:6'],
        ];
    }
}
