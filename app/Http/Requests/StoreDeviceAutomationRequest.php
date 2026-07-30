<?php

namespace App\Http\Requests;

use App\Enums\DeviceAutomationStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDeviceAutomationRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:140'],
            'trigger_type' => [
                'required',
                Rule::in([
                    'safe-zone-exit', 'battery-low', 'device-offline',
                    'feeding-failed', 'water-low', 'temperature-high',
                    'temperature-low', 'door-open', 'leak-detected',
                ]),
            ],
            'trigger_value' => ['nullable', 'numeric', 'between:-100000,100000'],
            'condition_mode' => [
                'required',
                Rule::in(['any', 'home', 'away', 'sitter', 'night', 'lost-mode']),
            ],
            'action_type' => [
                'required',
                Rule::in([
                    'send-notification', 'create-task', 'lock-door',
                    'stop-water-pump', 'enable-lost-mode',
                ]),
            ],
            'priority' => [
                'required',
                Rule::in(['normal', 'important', 'urgent', 'critical']),
            ],
            'status' => [
                'required',
                Rule::in(array_column(DeviceAutomationStatus::cases(), 'value')),
            ],
            'max_runs_per_hour' => ['required', 'integer', 'between:1,12'],
            'cooldown_seconds' => ['required', 'integer', 'between:30,86400'],
            'safety_acknowledged' => ['accepted'],
        ];
    }
}
