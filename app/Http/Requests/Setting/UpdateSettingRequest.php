<?php

namespace App\Http\Requests\Setting;

use App\Http\Requests\BaseFormRequest;

class UpdateSettingRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'value' => ['nullable', 'string'],
        ] + $this->translationRules([
            'value' => ['sometimes', 'string'],
        ], requireDefaultLocale: false);
    }
}
