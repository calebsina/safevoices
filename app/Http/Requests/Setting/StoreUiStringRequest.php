<?php

namespace App\Http\Requests\Setting;

use App\Http\Requests\BaseFormRequest;

class StoreUiStringRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'key'   => ['required', 'string', 'max:150'],
            'group' => ['nullable', 'string', 'max:64'],
        ] + $this->translationRules([
            'value' => ['required_with:translations', 'string'],
        ]);
    }
}
