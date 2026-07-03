<?php

namespace App\Http\Requests\Cms;

use App\Http\Requests\BaseFormRequest;
use Illuminate\Validation\Rule;

class UpdatePageRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'slug'       => ['sometimes', 'string', 'max:150', 'alpha_dash', Rule::unique('pages', 'slug')->ignore($this->route('page'))],
            'key'        => ['nullable', 'string', 'max:64', 'alpha_dash', Rule::unique('pages', 'key')->ignore($this->route('page'))],
            'template'   => ['sometimes', 'string', 'max:64'],
            'status'     => ['sometimes', 'in:draft,published'],
            'parent_id'  => ['nullable', 'integer', 'exists:pages,id'],
            'sort_order' => ['sometimes', 'integer'],
        ] + $this->translationRules([
            'title'            => ['sometimes', 'string', 'max:200'],
            'localized_slug'   => ['nullable', 'string', 'max:150'],
            'body'             => ['nullable', 'string'],
            'meta_title'       => ['nullable', 'string', 'max:200'],
            'meta_description' => ['nullable', 'string', 'max:300'],
        ], requireDefaultLocale: false);
    }
}
