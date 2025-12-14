<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLanguagesRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'languages' => 'required|array|min:1',
            'languages.*.language_id' => 'required|exists:languages,id',
            'languages.*.proficiency_level' => 'required|in:A1,A2,B1,B2,C1,C2,Native',
            'languages.*.can_help' => 'boolean',
        ];
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'languages.required' => 'Please add at least one language.',
            'languages.*.language_id.exists' => 'Invalid language selected.',
            'languages.*.proficiency_level.in' => 'Please select a valid proficiency level.',
        ];
    }
}
