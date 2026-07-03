<?php

namespace App\Http\Requests\FollowUp;

use App\Http\Requests\BaseFormRequest;

class AddInformationRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'body' => ['required', 'string', 'max:10000'],
        ];
    }
}
