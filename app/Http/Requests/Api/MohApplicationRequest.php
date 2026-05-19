<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class MohApplicationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Only allow users with ROLE_MOH
        return $this->user() && $this->user()->isMinistry();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'national_id' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    if (! validatePalestinianId($value)) {
                        $fail('رقم الهوية غير صالح.');
                    }
                },
            ],
            'first_name' => 'required|string|max:50',
            'father_name' => 'required|string|max:50',
            'grandfather_name' => 'required|string|max:50',
            'family_name' => 'required|string|max:50',
            'gender' => 'required|integer|in:1,2',
            'dob' => 'required|date_format:Y-m-d',
            'phone_number' => [
                'required',
                'string',
                'regex:/^(5[0-9]{8}|05[0-9]{8}|9705[0-9]{8}|9725[0-9]{8})$/',
            ],
            'street' => 'required|string|max:255',
            'section_id' => 'required|integer|exists:sections,id',
            'start_date' => 'required|date_format:Y-m-d',
            'training_hours' => 'required|integer|min:1',
        ];
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'The given data was invalid.',
            'errors' => $validator->errors(),
        ], 422));
    }
}
