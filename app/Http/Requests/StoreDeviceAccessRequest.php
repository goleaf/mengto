<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDeviceAccessRequest extends FormRequest
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
            'recipient_name' => ['required', 'string', 'max:120'],
            'recipient_role' => [
                'required',
                Rule::in(['co-owner', 'sitter', 'veterinarian', 'trainer', 'support']),
            ],
            'label' => ['required', 'string', 'max:140'],
            'permissions' => ['required', 'array', 'min:1'],
            'permissions.*' => [
                'required',
                Rule::in(['view-status', 'view-readings', 'view-events', 'control']),
            ],
            'allow_location' => ['nullable', 'boolean'],
            'allow_camera' => ['nullable', 'boolean'],
            'allow_commands' => ['nullable', 'boolean'],
            'allow_audio' => ['nullable', 'boolean'],
            'max_views' => ['required', 'integer', 'between:1,100'],
            'expires_in_hours' => ['required', 'integer', 'between:1,720'],
            'privacy_acknowledged' => ['accepted'],
        ];
    }
}
