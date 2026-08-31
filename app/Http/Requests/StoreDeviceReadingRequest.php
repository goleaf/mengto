<?php

namespace App\Http\Requests;

use App\Enums\DeviceConfidence;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDeviceReadingRequest extends FormRequest
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
            'external_event_id' => ['required', 'string', 'max:160'],
            'pet_profile_key' => [
                'nullable',
                'string',
                'max:120',
                'regex:/^[a-z0-9-]+$/',
            ],
            'metric_type' => [
                'required',
                Rule::in([
                    'location', 'activity-minutes', 'sleep-minutes',
                    'food-dispensed', 'water-use', 'litter-visit',
                    'weight-grams', 'temperature-c', 'humidity-percent',
                    'door-use', 'battery-percent',
                ]),
            ],
            'numeric_value' => ['nullable', 'numeric', 'between:-1000000,1000000'],
            'text_value' => ['nullable', 'string', 'max:255'],
            'unit' => ['nullable', 'string', 'max:40'],
            'recorded_at' => ['required', 'date'],
            'timezone' => ['required', 'timezone'],
            'accuracy_meters' => ['nullable', 'numeric', 'between:0,100000'],
            'confidence' => [
                'required',
                Rule::in(array_column(DeviceConfidence::cases(), 'value')),
            ],
            'original_payload' => ['nullable', 'array', 'max:20'],
            'processed_payload' => ['nullable', 'array', 'max:20'],
            'is_stale' => ['nullable', 'boolean'],
        ];
    }
}
