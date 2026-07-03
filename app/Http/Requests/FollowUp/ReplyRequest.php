<?php

namespace App\Http\Requests\FollowUp;

use App\Http\Requests\BaseFormRequest;

class ReplyRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'body' => ['required', 'string', 'max:5000'],
        ];
    }
}
