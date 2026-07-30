<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreListingReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return [
            'item_rating' => ['required', 'integer', 'between:1,5'],
            'seller_rating' => ['required', 'integer', 'between:1,5'],
            'delivery_rating' => ['nullable', 'integer', 'between:1,5'],
            'body' => ['required', 'string', 'min:20', 'max:3000'],
        ];
    }
}
