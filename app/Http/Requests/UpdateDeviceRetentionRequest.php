<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateDeviceRetentionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isActive() === true;
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return [
            'location_retention_days' => ['required', 'integer', Rule::in([0, 1, 7, 30, 90, 365])],
            'media_retention_days' => ['required', 'integer', Rule::in([0, 1, 3, 7, 30, 90])],
            'telemetry_retention_days' => ['required', 'integer', Rule::in([30, 90, 365, 730])],
        ];
    }
}
