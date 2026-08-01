<?php

namespace App\Http\Requests;

use App\Enums\MedicalKnowledgeStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMedicalRecordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return [
            'pet_profile_key' => ['required', 'string', 'max:80'],
            'birth_date' => ['nullable', 'date', 'before_or_equal:today'],
            'birth_date_estimated' => ['nullable', 'boolean'],
            'sex' => ['nullable', Rule::in(['male', 'female', 'intersex', 'unknown'])],
            'reproductive_status' => [
                'required',
                Rule::in(['intact', 'spayed', 'neutered', 'unknown', 'planned', 'medical-restriction']),
            ],
            'weight' => ['nullable', 'numeric', 'min:0.001', 'max:2000'],
            'weight_unit' => ['required', Rule::in(['kg', 'g', 'lb', 'oz'])],
            'timezone' => ['required', 'timezone:all'],
            'microchip_status' => [
                'required',
                Rule::in(['registered', 'present', 'absent', 'unknown']),
            ],
            'microchip_number' => ['nullable', 'string', 'max:80'],
            'microchip_checked_on' => ['nullable', 'date', 'before_or_equal:today'],
            'blood_group' => ['nullable', 'string', 'max:60'],
            'allergy_knowledge_status' => ['required', Rule::enum(MedicalKnowledgeStatus::class)],
            'critical_allergies' => ['nullable', 'string', 'max:2000'],
            'medication_knowledge_status' => ['required', Rule::enum(MedicalKnowledgeStatus::class)],
            'chronic_conditions' => ['nullable', 'string', 'max:3000'],
            'emergency_notes' => ['nullable', 'string', 'max:3000'],
            'primary_clinic_name' => ['nullable', 'string', 'max:160'],
            'primary_clinic_contact' => ['nullable', 'string', 'max:500'],
            'emergency_contact_name' => ['nullable', 'string', 'max:120'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:80'],
            'emergency_contact_relationship' => ['nullable', 'string', 'max:80'],
            'privacy_acknowledged' => ['accepted'],
        ];
    }
}
