<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\CareEntryStatus;
use App\Enums\CareEntryType;
use App\Enums\CareSourceType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCareEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return [
            'idempotency_key' => ['required', 'uuid'],
            'care_task_id' => ['nullable', 'integer', 'exists:care_tasks,id'],
            'entry_type' => [
                'required',
                Rule::in(array_column(CareEntryType::cases(), 'value')),
            ],
            'subtype' => ['nullable', 'string', 'max:80'],
            'title' => ['required', 'string', 'max:180'],
            'started_at' => ['required', 'date'],
            'ended_at' => ['nullable', 'date', 'after_or_equal:started_at'],
            'source_recorded_at' => ['nullable', 'date'],
            'source_timezone' => ['nullable', 'timezone:all'],
            'submitted_offline' => ['nullable', 'boolean'],
            'status' => [
                'required',
                Rule::in(array_column(CareEntryStatus::cases(), 'value')),
            ],
            'source_type' => [
                'required',
                Rule::in(array_column(CareSourceType::cases(), 'value')),
            ],
            'source_name' => ['nullable', 'string', 'max:120'],
            'quantity_value' => ['nullable', 'numeric', 'min:0', 'max:1000000'],
            'quantity_unit' => [
                'nullable',
                Rule::in([
                    'g', 'kg', 'ml', 'l', 'oz', 'fl-oz', 'cups',
                    'pieces', 'packets', 'times', 'unknown',
                ]),
            ],
            'duration_minutes' => ['nullable', 'integer', 'min:0', 'max:10080'],
            'distance_meters' => ['nullable', 'integer', 'min:0', 'max:1000000'],
            'appetite' => [
                'nullable',
                Rule::in([
                    'usual', 'good', 'increased', 'reduced', 'strongly-reduced',
                    'refused', 'selective', 'unknown',
                ]),
            ],
            'intensity' => [
                'nullable',
                Rule::in(['very-low', 'low', 'moderate', 'high', 'very-high', 'unknown']),
            ],
            'product_name' => ['nullable', 'string', 'max:180'],
            'amount_offered' => ['nullable', 'string', 'max:120'],
            'amount_consumed' => ['nullable', 'string', 'max:120'],
            'water_source' => ['nullable', 'string', 'max:120'],
            'location_label' => ['nullable', 'string', 'max:180'],
            'route_summary' => ['nullable', 'string', 'max:500'],
            'toilet_quality' => ['nullable', 'string', 'max:120'],
            'sleep_quality' => ['nullable', 'string', 'max:120'],
            'mood' => ['nullable', 'string', 'max:120'],
            'trigger' => ['nullable', 'string', 'max:500'],
            'result' => ['nullable', 'string', 'max:500'],
            'temperature_c' => ['nullable', 'numeric', 'min:-80', 'max:100'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'is_unusual' => ['nullable', 'boolean'],
            'confirm_duplicate' => ['nullable', 'boolean'],
            'media' => [
                'nullable',
                'file',
                'mimes:jpg,jpeg,png,webp,mp4,mov',
                'mimetypes:image/jpeg,image/png,image/webp,video/mp4,video/quicktime',
                'max:15360',
            ],
            'media_alt' => ['nullable', 'string', 'max:500'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'source_type' => $this->input('source_type', 'owner'),
            'status' => $this->input('status', 'completed'),
            'source_name' => $this->input('source_name', ''),
            'source_timezone' => $this->input('source_timezone', ''),
            'submitted_offline' => $this->boolean('submitted_offline'),
        ]);
    }
}
