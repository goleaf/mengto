<?php

namespace App\Http\Requests;

use App\Enums\MedicalSourceType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMedicalDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:180'],
            'document_type' => [
                'required',
                Rule::in([
                    'visit-summary', 'lab-result', 'vaccination-certificate',
                    'prescription', 'imaging', 'surgery', 'insurance',
                    'travel', 'invoice', 'other',
                ]),
            ],
            'source_type' => [
                'required',
                Rule::in(array_column(MedicalSourceType::cases(), 'value')),
            ],
            'source_name' => ['nullable', 'string', 'max:160'],
            'expires_on' => ['nullable', 'date'],
            'document' => [
                'required',
                'file',
                'mimes:pdf,jpg,jpeg,png,webp,mp4,mov,mp3,wav',
                'max:20480',
            ],
        ];
    }
}
