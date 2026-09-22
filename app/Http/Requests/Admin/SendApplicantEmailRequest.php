<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SendApplicantEmailRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() === true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'message_type' => ['required', Rule::in(['general', 'award'])],
            'mark_as_winner' => $this->input('message_type') === 'award'
                ? ['required', 'accepted']
                : ['exclude'],
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:10000'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'mark_as_winner.required' => 'Confirm that this approved applicant is an award winner.',
            'mark_as_winner.accepted' => 'Confirm that this approved applicant is an award winner.',
        ];
    }
}
