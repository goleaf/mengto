<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDeviceCommandRequest extends FormRequest
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
            'idempotency_key' => ['required', 'uuid'],
            'command_type' => [
                'required',
                Rule::in([
                    'refresh-status', 'enable-lost-mode', 'disable-lost-mode',
                    'dispense-food', 'stop-water-pump', 'start-water-pump',
                    'enable-privacy-mode', 'disable-privacy-mode',
                    'lock-door', 'unlock-door', 'clean-litter', 'locate-device',
                ]),
            ],
            'portion_grams' => ['nullable', 'numeric', 'between:1,1000'],
            'duration_minutes' => ['nullable', 'integer', 'between:1,1440'],
            'reason' => ['nullable', 'string', 'max:500'],
            'confirmed' => ['nullable', 'boolean'],
            'confirm_duplicate' => ['nullable', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'confirmed' => $this->boolean('confirmed'),
            'confirm_duplicate' => $this->boolean('confirm_duplicate'),
        ]);
    }
}
