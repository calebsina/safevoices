<?php

namespace App\Http\Requests\Office;

use App\Http\Requests\BaseFormRequest;
use Illuminate\Validation\Rule;

class UpdateOfficeRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'key'       => ['sometimes', 'string', 'max:64', 'alpha_dash', Rule::unique('offices', 'key')->ignore($this->route('office'))],
            'region'    => ['nullable', 'string', 'max:100'],
            'is_active' => ['sometimes', 'boolean'],
        ] + $this->translationRules([
            'name' => ['sometimes', 'string', 'max:150'],
        ], requireDefaultLocale: false);
    }
}
