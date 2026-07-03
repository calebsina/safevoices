<?php

namespace App\Http\Requests\Referral;

use App\Enums\ReferralStatus;
use App\Http\Requests\BaseFormRequest;
use Illuminate\Validation\Rule;

class UpdateReferralRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'status' => ['required', Rule::enum(ReferralStatus::class)],
            'notes'  => ['nullable', 'string', 'max:10000'],
        ];
    }
}
