<?php

namespace App\Http\Requests;

use App\Services\ListingSafety;
use App\Services\ListingTaxonomy;
use App\ValueObjects\MinorUnitAmount;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreListingRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $prepared = [
            'seller_type' => $this->input('seller_type', 'private'),
            'quantity' => $this->input('quantity', 1),
            'availability' => $this->input(
                'availability',
                $this->input('type') === 'rental' ? 'available-for-rent' : 'in-stock',
            ),
        ];

        foreach (['price', 'deposit_amount'] as $field) {
            $value = $this->input($field);

            if (is_scalar($value) && preg_match('/^\d+(?:\.\d{1,2})?$/', (string) $value) === 1) {
                $prepared[$field] = MinorUnitAmount::fromDecimal((string) $value)->toDecimal();
            }
        }

        $this->merge($prepared);
    }

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
            'type' => ['required', Rule::in(array_keys($taxonomy->types()))],
            'category' => ['required', Rule::in(array_keys($taxonomy->categories()))],
            'seller_type' => ['required', Rule::in(array_keys($taxonomy->sellerTypes()))],
            'title' => ['required', 'string', 'min:12', 'max:120'],
            'description' => ['required', 'string', 'min:50', 'max:5000'],
            'condition' => ['required', Rule::in(array_keys($taxonomy->conditions()))],
            'brand' => ['nullable', 'string', 'max:120'],
            'model' => ['nullable', 'string', 'max:120'],
            'material' => ['nullable', 'string', 'max:120'],
            'price' => ['nullable', 'numeric', 'decimal:0,2', 'min:0', 'max:999999.99'],
            'currency' => ['required', Rule::in(['EUR'])],
            'is_free' => ['nullable', 'boolean'],
            'quantity' => ['required', 'integer', 'min:1', 'max:100000'],
            'availability' => ['required', Rule::in(array_keys($taxonomy->availabilityOptions()))],
            'exchange_preferences' => ['nullable', 'string', 'max:1000'],
            'species' => ['required', 'array', 'min:1'],
            'species.*' => ['required', Rule::in(array_keys($taxonomy->species()))],
            'pet_size' => ['nullable', Rule::in(['small', 'medium', 'large', 'any'])],
            'age_group' => ['nullable', Rule::in(array_keys($taxonomy->ageGroups()))],
            'length_cm' => ['nullable', 'numeric', 'min:0.1', 'max:100000'],
            'width_cm' => ['nullable', 'numeric', 'min:0.1', 'max:100000'],
            'height_cm' => ['nullable', 'numeric', 'min:0.1', 'max:100000'],
            'max_weight_kg' => ['nullable', 'numeric', 'min:0.1', 'max:100000'],
            'defects' => [
                'nullable',
                'string',
                'max:2000',
                Rule::requiredIf(fn (): bool => in_array($this->string('condition')->toString(), ['fair', 'repair'], true)),
            ],
            'hygiene_status' => ['nullable', Rule::in(array_keys($taxonomy->hygieneStatuses()))],
            'sealed_package' => ['nullable', 'boolean'],
            'city' => ['required', 'string', 'max:80'],
            'area' => ['nullable', 'string', 'max:100'],
            'delivery_options' => ['required', 'array', 'min:1'],
            'delivery_options.*' => ['required', Rule::in(array_keys($taxonomy->deliveryOptions()))],
            'meetup_notes' => ['nullable', 'string', 'max:1000'],
            'return_policy' => ['nullable', 'string', 'max:2000'],
            'cover_url' => ['nullable', 'url:http,https', 'max:2048'],
            'photos' => ['nullable', 'array', 'max:8'],
            'photos.*' => [
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:8192',
                'dimensions:min_width=32,min_height=32,max_width=12000,max_height=12000',
            ],
            'video' => ['nullable', 'file', 'mimes:mp4,mov,webm', 'max:30720'],
            'business_name' => [
                'nullable',
                'string',
                'max:120',
                Rule::requiredIf(fn (): bool => $this->string('seller_type')->toString() !== 'private'),
            ],
            'rental_rate_unit' => [
                'nullable',
                Rule::in(['day', 'week']),
                Rule::requiredIf(fn (): bool => $this->string('type')->toString() === 'rental'),
            ],
            'deposit_amount' => ['nullable', 'numeric', 'decimal:0,2', 'min:0', 'max:999999.99'],
            'available_from' => [
                'nullable',
                'date',
                Rule::requiredIf(fn (): bool => $this->string('type')->toString() === 'rental'),
            ],
            'available_until' => ['nullable', 'date', 'after_or_equal:available_from'],
            'minimum_days' => ['nullable', 'integer', 'min:1', 'max:365'],
            'maximum_days' => ['nullable', 'integer', 'gte:minimum_days', 'max:365'],
            'service_duration_minutes' => ['nullable', 'integer', 'min:15', 'max:1440'],
            'service_format' => ['nullable', Rule::in(['in-person', 'online', 'home-visit', 'group'])],
            'urgency' => ['nullable', Rule::in(['urgent', 'important', 'regular', 'wish'])],
            'received_quantity' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'needed_by' => ['nullable', 'date', 'after_or_equal:today'],
            'animal_name' => ['nullable', 'string', 'max:100'],
            'animal_age' => ['nullable', 'string', 'max:80'],
            'animal_sex' => ['nullable', Rule::in(['female', 'male', 'unknown'])],
            'temperament' => ['nullable', 'string', 'max:1500'],
            'adoption_conditions' => ['nullable', 'string', 'max:2000'],
            'expires_at' => ['nullable', 'date', 'after:today'],
            'intent' => ['required', Rule::in(['draft', 'publish'])],
            'safety_acknowledged' => ['accepted'],
        ];
    }

    public function after(ListingSafety $safety): array
    {
        return [
            function (Validator $validator) use ($safety): void {
                $type = $this->string('type')->toString();
                $isFree = $this->boolean('is_free');
                $price = $this->input('price');

                if (in_array($type, ['sale', 'service', 'rental'], true) && ! $isFree && blank($price)) {
                    $validator->errors()->add('price', __('messages.add_a_price_or_mark_the_listing_as_free'));
                }

                if (in_array($type, ['adoption', 'free', 'shelter-need'], true)
                    && ! blank($price)
                    && ! $validator->errors()->has('price')
                    && MinorUnitAmount::fromDecimal((string) $price)->isPositive()) {
                    $validator->errors()->add('price', __('messages.this_listing_type_cannot_include_a_sale_price'));
                }

                if ($type === 'adoption') {
                    foreach (['animal_name', 'animal_age', 'temperament', 'adoption_conditions'] as $field) {
                        if (blank($this->input($field))) {
                            $validator->errors()->add($field, __('messages.complete_the_adoption_profile_before_publishing'));
                        }
                    }
                }

                if ($type === 'shelter-need'
                    && (int) $this->input('received_quantity', 0) > (int) $this->input('quantity', 0)) {
                    $validator->errors()->add('received_quantity', __('messages.received_quantity_cannot_exceed_the_amount_needed'));
                }

                if ($this->string('intent')->toString() === 'publish') {
                    $assessment = $safety->assess($this->validatedInput());

                    foreach ($assessment['blocked'] as $message) {
                        $validator->errors()->add('description', $message);
                    }
                }
            },
        ];
    }

    /** @return array<string, mixed> */
    private function validatedInput(): array
    {
        return [
            'type' => $this->input('type'),
            'category' => $this->input('category'),
            'seller_type' => $this->input('seller_type'),
            'title' => $this->input('title'),
            'description' => $this->input('description'),
            'brand' => $this->input('brand'),
            'model' => $this->input('model'),
            'condition' => $this->input('condition'),
            'sealed_package' => $this->boolean('sealed_package'),
        ];
    }
}
