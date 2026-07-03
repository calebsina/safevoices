<?php

namespace App\Http\Requests\Reference;

use App\Http\Requests\BaseFormRequest;

/** Shared store/update request for the four small lookup lists. */
class LookupRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'key'        => ['required', 'string', 'max:64', 'alpha_dash'],
            'sort_order' => ['sometimes', 'integer'],
            'is_active'  => ['sometimes', 'boolean'],
        ] + $this->translationRules([
            'label'       => ['required_with:translations', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);
    }
}
