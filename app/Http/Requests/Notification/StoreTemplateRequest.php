<?php

namespace App\Http\Requests\Notification;

use App\Http\Requests\BaseFormRequest;

class StoreTemplateRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'key'                    => ['required', 'string', 'max:100', 'alpha_dash'],
            'channel'                => ['required', 'in:whatsapp,sms,email,push,portal'],
            'whatsapp_template_name' => ['nullable', 'string', 'max:150'],
            'is_active'              => ['sometimes', 'boolean'],
        ] + $this->translationRules([
            'subject' => ['nullable', 'string', 'max:255'],
            'body'    => ['required_with:translations', 'string'],
        ]);
    }
}
