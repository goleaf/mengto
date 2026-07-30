<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BrowseExpertsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'q' => ['nullable', 'string', 'max:120'],
            'type' => ['nullable', 'string', 'max:80'],
            'species' => ['nullable', 'string', 'max:80'],
            'specialization' => ['nullable', 'string', 'max:80'],
            'city' => ['nullable', 'string', 'max:100'],
            'language' => ['nullable', 'string', 'max:80'],
            'format' => ['nullable', 'string', 'max:60'],
            'availability' => ['nullable', Rule::in(['available', 'today', 'week', 'waitlist'])],
            'verified' => ['nullable', 'boolean'],
            'accessible' => ['nullable', 'string', 'max:80'],
            'sort' => ['nullable', Rule::in(['relevance', 'availability', 'rating', 'experience', 'newest'])],
        ];
    }
}
