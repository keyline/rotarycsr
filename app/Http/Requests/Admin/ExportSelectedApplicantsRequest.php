<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ExportSelectedApplicantsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() === true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'applicant_ids' => ['required', 'array', 'min:1'],
            'applicant_ids.*' => ['required', 'integer', 'distinct', Rule::exists('users', 'id')->where('role', 'applicant')],
            'format' => ['required', Rule::in(['pdf', 'xlsx'])],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'applicant_ids.required' => 'Select at least one applicant to export.',
            'applicant_ids.min' => 'Select at least one applicant to export.',
            'applicant_ids.*.exists' => 'One of the selected applicants is invalid.',
        ];
    }
}
