<?php

namespace App\Http\Requests;

use App\Services\ListingTaxonomy;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BrowseListingsRequest extends FormRequest
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
    public function rules(ListingTaxonomy $taxonomy): array
    {
        return [
            'q' => ['nullable', 'string', 'max:120'],
            'type' => ['nullable', Rule::in(array_keys($taxonomy->types()))],
            'category' => ['nullable', Rule::in(array_keys($taxonomy->categories()))],
            'species' => ['nullable', Rule::in(array_keys($taxonomy->species()))],
            'city' => ['nullable', 'string', 'max:80'],
            'delivery' => ['nullable', Rule::in(array_keys($taxonomy->deliveryOptions()))],
            'price' => ['nullable', Rule::in(array_keys($taxonomy->priceFilters()))],
            'condition' => ['nullable', Rule::in(array_keys($taxonomy->conditions()))],
            'seller_type' => ['nullable', Rule::in(array_keys($taxonomy->sellerTypes()))],
            'availability' => ['nullable', Rule::in(array_keys($taxonomy->availabilityOptions()))],
            'sort' => ['nullable', Rule::in(array_keys($taxonomy->sortOptions()))],
        ];
    }
}
