<?php

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
    public function rules(): array
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
                Rule::in(array_keys(app(ListingTaxonomy::class)->deliveryOptions())),
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
                Rule::in(array_keys(app(ListingTaxonomy::class)->reportReasons())),
                'required_if:action,report',
            ],
            'details' => ['nullable', 'string', 'max:2000'],
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
                    $validator->errors()->add('quantity', 'The requested quantity is no longer available.');
                }

                if ($listing->type->value === 'rental') {
                    if (blank($this->input('rental_starts_at')) || blank($this->input('rental_ends_at'))) {
                        $validator->errors()->add('rental_starts_at', 'Choose a rental start and end date.');
                    }
                }

                if ($listing->type->value === 'adoption') {
                    foreach (['experience', 'home_context', 'care_plan', 'adoption_reason'] as $field) {
                        if (blank($this->input($field))) {
                            $validator->errors()->add($field, 'Complete the adoption application before sending it.');
                        }
                    }
                }
            },
        ];
    }
}
