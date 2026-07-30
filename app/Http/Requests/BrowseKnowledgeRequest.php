<?php

namespace App\Http\Requests;

use App\Services\ForumTaxonomy;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BrowseKnowledgeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
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
            'q' => ['nullable', 'string', 'max:160'],
            'category' => [
                'nullable',
                Rule::in(['all', ...array_keys(app(ForumTaxonomy::class)->categoryOptions())]),
            ],
            'type' => ['nullable', Rule::in(['all', 'guide', 'checklist', 'faq', 'comparison', 'local-guide'])],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
