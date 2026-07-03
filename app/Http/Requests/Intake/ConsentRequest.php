<?php

namespace App\Http\Requests\Intake;

use App\Http\Requests\BaseFormRequest;

class ConsentRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'data_use_consent' => ['required', 'boolean', 'accepted'], // required to proceed
            'contact_consent'  => ['required', 'boolean'],             // may be declined
        ];
    }
}
