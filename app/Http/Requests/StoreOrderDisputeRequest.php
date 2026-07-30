<?php

namespace App\Http\Requests;

use App\Services\ListingTaxonomy;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOrderDisputeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return [
            'reason' => ['required', Rule::in(array_keys(app(ListingTaxonomy::class)->disputeReasons()))],
            'details' => ['required', 'string', 'min:20', 'max:4000'],
        ];
    }
}
