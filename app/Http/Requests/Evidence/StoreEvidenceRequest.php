<?php

namespace App\Http\Requests\Evidence;

use App\Http\Requests\BaseFormRequest;

class StoreEvidenceRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'file' => [
                'required',
                'file',
                'max:'.config('safevoice.evidence.max_kilobytes'),
                'mimes:'.implode(',', config('safevoice.evidence.allowed_mimes')),
            ],
        ];
    }
}
