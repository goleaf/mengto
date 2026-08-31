<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class BrowseComposerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'pet' => [
                'nullable',
                'string',
                'max:120',
                'regex:/^[a-z0-9-]+$/',
            ],
            'target' => [
                'nullable',
                'string',
                'max:80',
                'regex:/^[a-z0-9-]+$/',
            ],
            'post' => ['nullable', 'string', 'max:80', 'regex:/^[a-z0-9-]+$/'],
            'place' => ['nullable', 'string', 'max:80', 'regex:/^[a-z0-9-]+$/'],
            'label' => ['nullable', 'string', 'max:120'],
        ];
    }
}
