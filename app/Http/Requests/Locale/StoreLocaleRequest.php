<?php

namespace App\Http\Requests\Locale;

use App\Http\Requests\BaseFormRequest;

class StoreLocaleRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'code'       => ['required', 'string', 'max:5', 'unique:locales,code'],
            'name'       => ['required', 'string', 'max:50'],
            'is_default' => ['sometimes', 'boolean'],
            'is_active'  => ['sometimes', 'boolean'],
            'sort_order' => ['sometimes', 'integer'],
        ];
    }
}
