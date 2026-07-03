<?php

namespace App\Http\Requests\Report;

use App\Http\Requests\BaseFormRequest;

class UpdateStatusRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'status_id' => ['required', 'integer', 'exists:case_statuses,id'],
            'note'      => ['nullable', 'string', 'max:500'],
        ];
    }
}
