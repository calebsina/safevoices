<?php

namespace App\Http\Requests\Message;

use App\Http\Requests\BaseFormRequest;

class StoreMessageRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'body' => ['required', 'string', 'max:5000'],
        ];
    }
}
