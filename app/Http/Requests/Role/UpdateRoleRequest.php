<?php

namespace App\Http\Requests\Role;

use App\Http\Requests\BaseFormRequest;
use Illuminate\Validation\Rule;

class UpdateRoleRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'key'           => ['sometimes', 'string', 'max:64', 'alpha_dash', Rule::unique('roles', 'key')->ignore($this->route('role'))],
            'permissions'   => ['sometimes', 'array'],
            'permissions.*' => ['integer', 'exists:permissions,id'],
        ] + $this->translationRules([
            'label'       => ['sometimes', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:255'],
        ], requireDefaultLocale: false);
    }
}
