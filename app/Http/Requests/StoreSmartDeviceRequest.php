<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\DeviceType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSmartDeviceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->isActive() === true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'type' => ['required', Rule::in(array_column(DeviceType::cases(), 'value'))],
            'brand' => ['nullable', 'string', 'max:100'],
            'model' => ['nullable', 'string', 'max:120'],
            'serial_number' => ['nullable', 'string', 'max:255'],
            'pet_profile_keys' => ['required', 'array', 'min:1', 'max:2'],
            'pet_profile_keys.*' => ['required', 'string', 'max:120', 'distinct'],
            'public_zone_label' => ['nullable', 'string', 'max:160'],
            'private_location_label' => ['nullable', 'string', 'max:500'],
            'connection_type' => [
                'nullable',
                Rule::in(['wi-fi', 'bluetooth', 'cellular', 'radio', 'matter', 'manual']),
            ],
            'firmware_version' => ['nullable', 'string', 'max:80'],
            'battery_percent' => ['nullable', 'integer', 'between:0,100'],
            'has_backup_power' => ['nullable', 'boolean'],
            'supports_local_operation' => ['nullable', 'boolean'],
            'requires_cloud' => ['nullable', 'boolean'],
            'is_medical_device' => ['nullable', 'boolean'],
            'ownership_confirmed' => ['accepted'],
            'privacy_acknowledged' => ['accepted'],
        ];
    }
}
