<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreCareJournalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isActive() === true;
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return [
            'pet_profile_key' => ['required', 'string', 'max:120'],
            'timezone' => ['required', 'timezone:all'],
            'current_caregiver_name' => ['nullable', 'string', 'max:120'],
            'privacy_acknowledged' => ['accepted'],
        ];
    }
}
