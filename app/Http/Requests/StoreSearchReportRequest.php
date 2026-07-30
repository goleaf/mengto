<?php

namespace App\Http\Requests;

use App\Services\SearchTaxonomy;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSearchReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return [
            'sighting_id' => ['nullable', 'integer', 'exists:sightings,id'],
            'reason' => ['required', Rule::in(array_keys(app(SearchTaxonomy::class)->reportReasons()))],
            'details' => ['nullable', 'string', 'max:2500'],
        ];
    }
}
