<?php

namespace App\Http\Requests\Locale;

use App\Http\Requests\BaseFormRequest;
use Illuminate\Validation\Rule;

class UpdateLocaleRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'code'       => ['sometimes', 'string', 'max:5', Rule::unique('locales', 'code')->ignore($this->route('locale'))],
            'name'       => ['sometimes', 'string', 'max:50'],
            'is_default' => ['sometimes', 'boolean'],
            'is_active'  => ['sometimes', 'boolean'],
            'sort_order' => ['sometimes', 'integer'],
        ];
    }
}
