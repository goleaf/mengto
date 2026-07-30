<?php

namespace App\Http\Requests;

use App\Services\SearchTaxonomy;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BrowseSearchCasesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        $taxonomy = app(SearchTaxonomy::class);

        return [
            'q' => ['nullable', 'string', 'max:120'],
            'type' => ['nullable', Rule::in(array_keys($taxonomy->types()))],
            'status' => ['nullable', Rule::in(array_keys($taxonomy->directoryStatuses()))],
            'species' => ['nullable', Rule::in(array_keys($taxonomy->species()))],
            'city' => ['nullable', 'string', 'max:100'],
            'sort' => ['nullable', Rule::in(array_keys($taxonomy->sortOptions()))],
        ];
    }
}
