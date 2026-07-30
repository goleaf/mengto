<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreAnswerRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'body' => ['required', 'string', 'min:20', 'max:6000'],
            'experience_type' => ['required', 'in:personal-experience,volunteer-experience,professional-opinion,organization-experience,source-summary'],
            'sources' => ['nullable', 'string', 'max:1500'],
        ];
    }
}
