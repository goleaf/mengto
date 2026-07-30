<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCareRoutineRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:160'],
            'period' => [
                'required',
                Rule::in(['daily', 'weekdays', 'weekends', 'weekly', 'temporary', 'custom']),
            ],
            'starts_on' => ['required', 'date'],
            'ends_on' => ['nullable', 'date', 'after_or_equal:starts_on'],
            'days' => ['nullable', 'array', 'max:7'],
            'days.*' => [
                'string',
                Rule::in(['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday']),
            ],
            'start_time' => ['nullable', 'date_format:H:i'],
            'instructions' => ['nullable', 'string', 'max:3000'],
        ];
    }
}
