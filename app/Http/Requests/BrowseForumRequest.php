<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Services\ForumTaxonomy;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BrowseForumRequest extends FormRequest
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
    public function rules(ForumTaxonomy $taxonomy): array
    {
        return [
            'q' => ['nullable', 'string', 'max:160'],
            'category' => ['nullable', Rule::in(['all', ...$taxonomy->acceptedBrowseCategoryKeys()])],
            'filter' => ['nullable', Rule::in(array_keys($taxonomy->filterOptions()))],
            'sort' => ['nullable', Rule::in(array_keys($taxonomy->sortOptions()))],
            'language' => ['nullable', Rule::in(['all', 'en', 'lt', 'ru'])],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
