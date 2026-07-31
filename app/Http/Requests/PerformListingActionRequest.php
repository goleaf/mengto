<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Listing;
use App\Services\ListingTaxonomy;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class PerformListingActionRequest extends FormRequest
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
        $actions = [
            'toggle-save',
            'request',
            'cancel-request',
            'accept-request',
            'decline-request',
            'mark-complete',
            'report',
        ];

        return [
            'action' => ['required', Rule::in($actions)],
            'reservation_id' => [
                'nullable',
                'integer',
                'required_if:action,cancel-request,accept-request,decline-request,mark-complete',
            ],
            'idempotency_key' => ['nullable', 'uuid', 'required_if:action,request'],
            'message' => ['nullable', 'string', 'min:10', 'max:1500', 'required_if:action,request'],
            'quantity' => ['nullable', 'integer', 'min:1', 'max:100000', 'required_if:action,request'],
            'offered_price' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
            'exchange_method' => [
                'nullable',
                Rule::in(array_keys($taxonomy->deliveryOptions())),
                'required_if:action,request',
            ],
            'proposed_at' => ['nullable', 'date', 'after:now'],
            'rental_starts_at' => ['nullable', 'date', 'after_or_equal:today'],
            'rental_ends_at' => ['nullable', 'date', 'after:rental_starts_at'],
            'experience' => ['nullable', 'string', 'max:1500'],
            'home_context' => ['nullable', 'string', 'max:1500'],
            'other_pets' => ['nullable', 'string', 'max:1000'],
            'care_plan' => ['nullable', 'string', 'max:1500'],
            'adoption_reason' => ['nullable', 'string', 'max:1500'],
            'terms_accepted' => ['exclude_unless:action,request', 'required', 'accepted'],
            'privacy_accepted' => ['exclude_unless:action,request', 'required', 'accepted'],
            'reason' => [
                'nullable',
                Rule::in(array_keys($taxonomy->reportReasons())),
                'required_if:action,report',
            ],
            'details' => ['nullable', 'string', 'max:2000'],
            'truthfulness_confirmed' => [
                'exclude_unless:action,report',
                'required',
                'accepted',
            ],
            'immediate_safety' => [
                'exclude_unless:action,report',
                'sometimes',
                'boolean',
            ],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($this->string('action')->toString() !== 'request') {
                    return;
                }

                $listing = $this->route('listing');
                if (! $listing instanceof Listing) {
                    return;
                }

                if ((int) $this->input('quantity', 1) > $listing->quantity) {
                    $validator->errors()->add('quantity', __('messages.the_requested_quantity_is_no_longer_available_91358bffe6'));
                }

                if ($listing->type->value === 'rental') {
                    if (blank($this->input('rental_starts_at')) || blank($this->input('rental_ends_at'))) {
                        $validator->errors()->add('rental_starts_at', __('messages.choose_a_rental_start_and_end_date_530d30c6d9'));
                    }
                }

                if ($listing->type->value === 'adoption') {
                    foreach (['experience', 'home_context', 'care_plan', 'adoption_reason'] as $field) {
                        if (blank($this->input($field))) {
                            $validator->errors()->add($field, __('messages.complete_the_adoption_application_before_sending_it_3ec75b99ba'));
                        }
                    }
                }
            },
        ];
    }
}
