<?php

namespace App\Http\Requests\Intake;

use App\Http\Requests\BaseFormRequest;

class IncidentRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'category_id'        => ['required', 'integer', 'exists:case_categories,id'],
            'description'        => ['required', 'string', 'max:10000'],
            'incident_area'      => ['nullable', 'string', 'max:255'], // approximate area only
            'incident_at'        => ['nullable', 'date', 'before_or_equal:now'],
            'is_imminent_danger' => ['sometimes', 'boolean'],
        ];
    }
}
