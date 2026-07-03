<?php

namespace App\Http\Requests\Reference;

use App\Http\Requests\BaseFormRequest;
use Illuminate\Validation\Rule;

class UpdateCaseCategoryRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'key'             => ['sometimes', 'string', 'max:64', 'alpha_dash', Rule::unique('case_categories', 'key')->ignore($this->route('category'))],
            'parent_id'       => ['nullable', 'integer', 'exists:case_categories,id'],
            'severity_weight' => ['sometimes', 'integer', 'min:0', 'max:100'],
            'sort_order'      => ['sometimes', 'integer'],
            'is_active'       => ['sometimes', 'boolean'],
        ] + $this->translationRules([
            'name'        => ['sometimes', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:255'],
        ], requireDefaultLocale: false);
    }
}
