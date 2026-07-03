<?php

namespace App\Http\Requests\Reference;

use App\Http\Requests\BaseFormRequest;

class StoreCaseCategoryRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'key'             => ['required', 'string', 'max:64', 'unique:case_categories,key', 'alpha_dash'],
            'parent_id'       => ['nullable', 'integer', 'exists:case_categories,id'],
            'severity_weight' => ['sometimes', 'integer', 'min:0', 'max:100'],
            'sort_order'      => ['sometimes', 'integer'],
            'is_active'       => ['sometimes', 'boolean'],
        ] + $this->translationRules([
            'name'        => ['required_with:translations', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);
    }
}
