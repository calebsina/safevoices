<?php

namespace App\Http\Requests\Cms;

use App\Http\Requests\BaseFormRequest;

class StoreFaqRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'category'   => ['nullable', 'string', 'max:64'],
            'sort_order' => ['sometimes', 'integer'],
            'is_active'  => ['sometimes', 'boolean'],
        ] + $this->translationRules([
            'question' => ['required_with:translations', 'string', 'max:500'],
            'answer'   => ['required_with:translations', 'string'],
        ]);
    }
}
