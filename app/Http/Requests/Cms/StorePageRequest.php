<?php

namespace App\Http\Requests\Cms;

use App\Http\Requests\BaseFormRequest;

class StorePageRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'slug'       => ['required', 'string', 'max:150', 'unique:pages,slug', 'alpha_dash'],
            'key'        => ['nullable', 'string', 'max:64', 'unique:pages,key', 'alpha_dash'],
            'template'   => ['sometimes', 'string', 'max:64'],
            'status'     => ['sometimes', 'in:draft,published'],
            'parent_id'  => ['nullable', 'integer', 'exists:pages,id'],
            'sort_order' => ['sometimes', 'integer'],
        ] + $this->translationRules([
            'title'            => ['required_with:translations', 'string', 'max:200'],
            'localized_slug'   => ['nullable', 'string', 'max:150'],
            'body'             => ['nullable', 'string'],
            'meta_title'       => ['nullable', 'string', 'max:200'],
            'meta_description' => ['nullable', 'string', 'max:300'],
        ]);
    }
}
