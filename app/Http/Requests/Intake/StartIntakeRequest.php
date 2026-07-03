<?php

namespace App\Http\Requests\Intake;

use App\Http\Requests\BaseFormRequest;
use Illuminate\Validation\Rule;

class StartIntakeRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'channel' => ['required', 'string', Rule::exists('channels', 'key')],
            'locale'  => ['required', 'string', Rule::in(sv_locales())],
            // E.164; optional - a reporter may decline any contact info.
            'phone'   => ['nullable', 'string', 'regex:/^\+[1-9]\d{6,14}$/'],
        ];
    }
}
