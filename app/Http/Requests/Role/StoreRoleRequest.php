<?php

namespace App\Http\Requests\Role;

use App\Http\Requests\BaseFormRequest;

class StoreRoleRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'key'            => ['required', 'string', 'max:64', 'unique:roles,key', 'alpha_dash'],
            'permissions'    => ['sometimes', 'array'],
            'permissions.*'  => ['integer', 'exists:permissions,id'],
        ] + $this->translationRules([
            'label'       => ['required_with:translations', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);
    }
}
