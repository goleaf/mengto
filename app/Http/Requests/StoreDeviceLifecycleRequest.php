<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\DeviceLifecycleKind;
use App\Enums\DeviceLifecycleStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreDeviceLifecycleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isActive() === true;
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        $requiresConsequencesReview = in_array($this->input('kind'), [
            DeviceLifecycleKind::Transfer->value,
            DeviceLifecycleKind::Disposal->value,
            DeviceLifecycleKind::Recall->value,
            DeviceLifecycleKind::Vulnerability->value,
        ], true);

        return [
            'kind' => ['required', Rule::enum(DeviceLifecycleKind::class)],
            'status' => ['required', Rule::enum(DeviceLifecycleStatus::class)],
            'severity' => ['required', Rule::in(['normal', 'important', 'critical'])],
            'effective_at' => ['required', 'date'],
            'version_from' => ['nullable', 'string', 'max:80'],
            'version_to' => [
                Rule::requiredIf($this->input('kind') === DeviceLifecycleKind::Firmware->value),
                'nullable',
                'string',
                'max:80',
            ],
            'reference' => ['nullable', 'string', 'max:255'],
            'note' => ['nullable', 'string', 'max:2000'],
            'consequences_reviewed' => [
                Rule::excludeIf(! $requiresConsequencesReview),
                Rule::requiredIf($requiresConsequencesReview),
                'accepted',
            ],
            'block_remote_control' => ['nullable', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'block_remote_control' => $this->boolean('block_remote_control'),
        ]);
    }
}
