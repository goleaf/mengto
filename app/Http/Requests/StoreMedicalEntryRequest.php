<?php

namespace App\Http\Requests;

use App\Enums\MedicalEventType;
use App\Enums\MedicalSourceType;
use App\Enums\MedicationStatus;
use App\Enums\VaccinationStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMedicalEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return [
            'entry_type' => [
                'required',
                Rule::in(['event', 'weight', 'vaccination', 'medication', 'reminder']),
            ],
            'title' => ['required_unless:entry_type,weight', 'nullable', 'string', 'max:180'],
            'source_type' => [
                'nullable',
                Rule::in(array_column(MedicalSourceType::cases(), 'value')),
            ],
            'source_name' => ['nullable', 'string', 'max:160'],
            'source_reference' => ['nullable', 'string', 'max:160'],
            'professional_name' => ['nullable', 'string', 'max:160'],

            'event_type' => [
                'required_if:entry_type,event',
                'nullable',
                Rule::in(array_column(MedicalEventType::cases(), 'value')),
            ],
            'occurred_at' => ['required_if:entry_type,event', 'nullable', 'date'],
            'event_status' => [
                'nullable',
                Rule::in(['active', 'suspected', 'confirmed', 'controlled', 'resolved', 'superseded']),
            ],
            'summary' => ['nullable', 'string', 'max:5000'],
            'severity' => ['nullable', Rule::in(['mild', 'moderate', 'severe', 'critical'])],
            'next_step' => ['nullable', 'string', 'max:1000'],
            'follow_up_at' => ['nullable', 'date'],
            'is_critical' => ['nullable', 'boolean'],

            'weight' => ['required_if:entry_type,weight', 'nullable', 'numeric', 'min:0.001', 'max:2000'],
            'weight_unit' => [
                'required_if:entry_type,weight',
                'nullable',
                Rule::in(['kg', 'g', 'lb', 'oz']),
            ],
            'tare' => ['nullable', 'numeric', 'min:0', 'max:500'],
            'measured_at' => ['required_if:entry_type,weight', 'nullable', 'date'],
            'measurement_context' => ['nullable', 'string', 'max:120'],
            'notes' => ['nullable', 'string', 'max:2000'],

            'vaccination_status' => [
                'required_if:entry_type,vaccination',
                'nullable',
                Rule::in(array_column(VaccinationStatus::cases(), 'value')),
            ],
            'manufacturer' => ['nullable', 'string', 'max:160'],
            'lot_number' => ['nullable', 'string', 'max:120'],
            'product_expires_on' => ['nullable', 'date'],
            'administered_on' => ['nullable', 'date', 'before_or_equal:today'],
            'next_due_on' => ['nullable', 'date', 'after_or_equal:administered_on'],
            'reaction' => ['nullable', 'string', 'max:2000'],

            'medication_form' => [
                'required_if:entry_type,medication',
                'nullable',
                Rule::in([
                    'tablet', 'capsule', 'liquid', 'suspension', 'powder',
                    'drops', 'ointment', 'spray', 'injection', 'patch',
                    'implant', 'inhalation', 'other',
                ]),
            ],
            'active_ingredient' => ['nullable', 'string', 'max:160'],
            'concentration' => ['nullable', 'string', 'max:80'],
            'dose' => ['required_if:entry_type,medication', 'nullable', 'string', 'max:120'],
            'route' => ['required_if:entry_type,medication', 'nullable', 'string', 'max:80'],
            'schedule_type' => [
                'required_if:entry_type,medication',
                'nullable',
                Rule::in(['fixed', 'interval', 'as-needed', 'specific-days']),
            ],
            'schedule_text' => ['required_if:entry_type,medication', 'nullable', 'string', 'max:180'],
            'starts_on' => ['required_if:entry_type,medication', 'nullable', 'date'],
            'ends_on' => ['nullable', 'date', 'after_or_equal:starts_on'],
            'next_dose_at' => ['nullable', 'date'],
            'medication_status' => [
                'required_if:entry_type,medication',
                'nullable',
                Rule::in(array_column(MedicationStatus::cases(), 'value')),
            ],
            'reason' => ['nullable', 'string', 'max:180'],
            'instructions' => ['nullable', 'string', 'max:3000'],
            'is_high_risk' => ['nullable', 'boolean'],
            'remaining_quantity' => ['nullable', 'numeric', 'min:0', 'max:100000'],
            'remaining_unit' => ['nullable', 'string', 'max:40'],
            'expires_on' => ['nullable', 'date'],

            'reminder_type' => [
                'required_if:entry_type,reminder',
                'nullable',
                Rule::in([
                    'vaccination', 'medication', 'parasite-treatment',
                    'lab-test', 'appointment', 'follow-up', 'weight',
                    'wound-care', 'rehabilitation', 'document', 'prescription',
                ]),
            ],
            'due_at' => ['required_if:entry_type,reminder', 'nullable', 'date'],
            'priority' => [
                'required_if:entry_type,reminder',
                'nullable',
                Rule::in(['low', 'normal', 'important', 'critical']),
            ],
            'recipients' => ['nullable', 'array', 'max:8'],
            'recipients.*' => ['string', 'max:80'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'source_type' => $this->input('source_type', 'owner'),
            'source_name' => $this->input('source_name', ''),
        ]);
    }
}
