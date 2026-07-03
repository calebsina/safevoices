<?php

namespace App\Http\Requests\Report;

use App\Http\Requests\BaseFormRequest;

class AssignRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'assigned_to' => ['required', 'uuid', 'exists:users,id'],
            'office_id'   => ['nullable', 'integer', 'exists:offices,id'],
        ];
    }
}
