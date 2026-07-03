<?php

namespace App\Http\Requests\User;

use App\Http\Requests\BaseFormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'name'      => ['sometimes', 'string', 'max:150'],
            'email'     => ['sometimes', 'email', 'max:190', Rule::unique('users', 'email')->ignore($this->route('user'))],
            'phone'     => ['nullable', 'string', 'max:30'],
            'password'  => ['nullable', 'string', 'min:12'],
            'role_id'   => ['sometimes', 'integer', 'exists:roles,id'],
            'office_id' => ['nullable', 'integer', 'exists:offices,id'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
