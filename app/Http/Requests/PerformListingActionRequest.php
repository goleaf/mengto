<?php

namespace App\Http\Requests;

use App\Services\ListingTaxonomy;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'exchange_method' => [
                'nullable',
                Rule::in(array_keys(app(ListingTaxonomy::class)->deliveryOptions())),
                'required_if:action,request',
            ],
            'proposed_at' => ['nullable', 'date', 'after:now'],
            'reason' => [
                'nullable',
                Rule::in(array_keys(app(ListingTaxonomy::class)->reportReasons())),
                'required_if:action,report',
            ],
            'details' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
