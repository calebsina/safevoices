<?php

namespace App\Http\Requests;

use App\Helpers\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

/**
 * Base class for every FormRequest.
 *
 *  - JSON-envelope validation errors (422) instead of redirects.
 *  - authorize() defaults to true: authentication is enforced by route
 *    middleware and row-level access by Policies, keeping requests
 *    focused on validation only.
 *  - translationRules() builds the per-locale rule set for any payload
 *    following the "translations" convention:
 *
 *      { "translations": { "en": { "name": "..." }, "fr": { "name": "..." } } }
 */
abstract class BaseFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Build validation rules for translated fields.
     *
     * @param array<string,string|array> $fieldRules e.g. ['name' => 'required|string|max:150']
     * @param bool $requireDefaultLocale require at least the default locale block
     */
    protected function translationRules(array $fieldRules, bool $requireDefaultLocale = true): array
    {
        $default = config('app.locale', 'en');

        $rules = [
            'translations' => [$requireDefaultLocale ? 'required' : 'sometimes', 'array'],
        ];

        if ($requireDefaultLocale) {
            $rules["translations.$default"] = ['required', 'array'];
        }

        foreach ($fieldRules as $field => $fieldRule) {
            $rules["translations.*.$field"] = $fieldRule;
        }

        return $rules;
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            ApiResponse::error(__('messages.validation_failed'), 422, $validator->errors())
        );
    }
}
