<?php

namespace App\Http\Requests\Consent;

use App\Http\Requests\BaseFormRequest;

class StoreConsentVersionRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'version'        => ['required', 'string', 'max:20', 'unique:consent_versions,version'],
            'effective_from' => ['required', 'date'],
            'is_active'      => ['sometimes', 'boolean'],
        ] + $this->translationRules([
            'body' => ['required_with:translations', 'string'],
        ]);
    }
}
