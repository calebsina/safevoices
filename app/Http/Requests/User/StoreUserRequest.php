<?php

namespace App\Http\Requests\User;

use App\Http\Requests\BaseFormRequest;

class StoreUserRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'name'      => ['required', 'string', 'max:150'],
            'email'     => ['required', 'email', 'max:190', 'unique:users,email'],
            'phone'     => ['nullable', 'string', 'max:30'],
            'password'  => ['required', 'string', 'min:12'], // dossier: strong password policy
            'role_id'   => ['required', 'integer', 'exists:roles,id'],
            'office_id' => ['nullable', 'integer', 'exists:offices,id'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
