<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BrowseRequest extends FormRequest
{
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
            'q' => ['nullable', 'string', 'max:80'],
            'filter' => ['nullable', 'string', 'max:40'],
            'sort' => ['nullable', Rule::in(['recommended', 'name', 'closest', 'soonest', 'active'])],
            'conversation' => ['nullable', 'string', Rule::in(['ari', 'lena', 'noah', 'priya'])],
        ];
    }
}
