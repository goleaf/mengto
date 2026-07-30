<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDeviceSafeZoneRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:120'],
            'shape' => ['required', Rule::in(['circle', 'polygon'])],
            'public_area_label' => ['required', 'string', 'max:160'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'radius_meters' => [
                'required_if:shape,circle',
                'nullable',
                'numeric',
                'between:20,50000',
            ],
            'exit_delay_seconds' => ['required', 'integer', 'between:0,900'],
            'accuracy_threshold_meters' => ['required', 'numeric', 'between:5,1000'],
            'is_home' => ['nullable', 'boolean'],
            'always_active' => ['nullable', 'boolean'],
        ];
    }
}
