<?php

namespace App\Http\Requests\Report;

use App\Http\Requests\BaseFormRequest;

class EscalateRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'reason' => ['nullable', 'string', 'max:500'],
        ];
    }
}
