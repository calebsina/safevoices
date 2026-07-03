<?php

namespace App\Http\Requests\Intake;

use App\Enums\ReportingFor;
use App\Http\Requests\BaseFormRequest;
use Illuminate\Validation\Rule;

class ContextRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'affected_person_type_id' => ['required', 'integer', 'exists:affected_person_types,id'],
            'relationship_id'         => ['required', 'integer', 'exists:relationships,id'],
            'reporting_for'           => ['required', Rule::enum(ReportingFor::class)],
        ];
    }
}
