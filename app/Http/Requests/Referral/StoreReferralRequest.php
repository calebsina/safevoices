<?php

namespace App\Http\Requests\Referral;

use App\Http\Requests\BaseFormRequest;

class StoreReferralRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'partner_type_id' => ['required', 'integer', 'exists:referral_partner_types,id'],
            'partner_name'    => ['nullable', 'string', 'max:150'],
            'notes'           => ['nullable', 'string', 'max:10000'],
        ];
    }
}
