<?php

namespace App\Http\Requests;

use App\Enums\CareEntryType;
use App\Enums\CareTaskPriority;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCareTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return [
            'care_routine_id' => ['nullable', 'integer', 'exists:care_routines,id'],
            'title' => ['required', 'string', 'max:180'],
            'type' => [
                'required',
                Rule::in(array_column(CareEntryType::cases(), 'value')),
            ],
            'assignee_key' => ['nullable', 'string', 'max:80', 'regex:/^[a-z0-9-]+$/'],
            'assignee_name' => ['nullable', 'string', 'max:120'],
            'due_at' => ['required', 'date'],
            'repeat_rule' => ['nullable', 'string', 'max:120'],
            'priority' => [
                'required',
                Rule::in(array_column(CareTaskPriority::cases(), 'value')),
            ],
            'instructions' => ['nullable', 'string', 'max:3000'],
            'requires_individual_confirmation' => ['nullable', 'boolean'],
        ];
    }
}
