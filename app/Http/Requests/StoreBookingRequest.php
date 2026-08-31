<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'service_id' => ['required', 'integer', Rule::exists('services', 'id')],
            'availability_slot_id' => ['required', 'integer', Rule::exists('availability_slots', 'id')],
            'idempotency_key' => ['required', 'uuid'],
            'pet_key' => ['required', 'string', 'max:120', 'regex:/^[a-z0-9-]+$/'],
            'main_question' => ['required', 'string', 'min:20', 'max:3000'],
            'started_at' => ['nullable', 'string', 'max:300'],
            'tried' => ['nullable', 'string', 'max:2000'],
            'previous_professional' => ['nullable', 'string', 'max:1000'],
            'desired_result' => ['nullable', 'string', 'max:1000'],
            'access_needs' => ['nullable', 'string', 'max:1000'],
            'urgent_signs' => ['nullable', 'boolean'],
            'recording_consent' => ['nullable', 'boolean'],
            'terms_accepted' => ['accepted'],
            'data_consent' => ['accepted'],
            'document_label' => ['nullable', 'string', 'max:180'],
            'document_type' => ['nullable', 'string', 'max:80'],
            'document' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:10240'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($this->boolean('urgent_signs')) {
                    $validator->errors()->add(
                        'urgent_signs',
                        __('messages.do_not_wait_for_a_planned_consultation_call_an_emergency_clinic_and_confirm_it_can_receive_your_pet'),
                    );
                }
            },
        ];
    }
}
