<?php

namespace App\Http\Requests\CaseAction;

use App\Http\Requests\BaseFormRequest;

class StoreCaseActionRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'action_type' => ['required', 'string', 'max:64', 'alpha_dash'],
            'notes'       => ['nullable', 'string', 'max:10000'],
        ];
    }
}
