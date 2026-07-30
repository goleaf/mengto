<?php

namespace App\Http\Requests;

use App\Services\ListingTaxonomy;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreListingRequest extends FormRequest
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
        $taxonomy = app(ListingTaxonomy::class);

        return [
            'type' => ['required', Rule::in(array_keys($taxonomy->types()))],
            'category' => ['required', Rule::in(array_keys($taxonomy->categories()))],
            'title' => ['required', 'string', 'min:12', 'max:120'],
            'description' => ['required', 'string', 'min:50', 'max:5000'],
            'condition' => ['required', Rule::in(array_keys($taxonomy->conditions()))],
            'price' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
            'currency' => ['required', Rule::in(['EUR'])],
            'is_free' => ['nullable', 'boolean'],
            'exchange_preferences' => ['nullable', 'string', 'max:1000'],
            'species' => ['required', 'array', 'min:1'],
            'species.*' => ['required', Rule::in(array_keys($taxonomy->species()))],
            'pet_size' => ['nullable', Rule::in(['small', 'medium', 'large', 'any'])],
            'city' => ['required', 'string', 'max:80'],
            'area' => ['nullable', 'string', 'max:100'],
            'delivery_options' => ['required', 'array', 'min:1'],
            'delivery_options.*' => ['required', Rule::in(array_keys($taxonomy->deliveryOptions()))],
            'meetup_notes' => ['nullable', 'string', 'max:1000'],
            'cover_url' => ['nullable', 'url:http,https', 'max:2048'],
            'is_business' => ['nullable', 'boolean'],
            'business_name' => ['nullable', 'required_if:is_business,1', 'string', 'max:120'],
            'intent' => ['required', Rule::in(['draft', 'publish'])],
            'safety_acknowledged' => ['accepted'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $type = $this->string('type')->toString();
                $isFree = $this->boolean('is_free');
                $price = $this->input('price');

                if (in_array($type, ['sale', 'service'], true) && ! $isFree && blank($price)) {
                    $validator->errors()->add('price', 'Add a price or mark the listing as free.');
                }

                if ($type === 'adoption' && ! blank($price) && (float) $price > 0) {
                    $validator->errors()->add('price', 'Adoption listings cannot include a sale price.');
                }
            },
        ];
    }
}
