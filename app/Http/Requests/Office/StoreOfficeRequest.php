<?php

namespace App\Http\Requests\Office;

use App\Http\Requests\BaseFormRequest;

class StoreOfficeRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'key'       => ['required', 'string', 'max:64', 'unique:offices,key', 'alpha_dash'],
            'region'    => ['nullable', 'string', 'max:100'],
            'is_active' => ['sometimes', 'boolean'],
        ] + $this->translationRules([
            'name' => ['required_with:translations', 'string', 'max:150'],
        ]);
    }
}
