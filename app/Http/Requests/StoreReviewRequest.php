<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'booking_id' => ['required', 'integer'],
            'rating' => ['required', 'integer', 'between:1,5'],
            'communication_rating' => ['required', 'integer', 'between:1,5'],
            'clarity_rating' => ['required', 'integer', 'between:1,5'],
            'organization_rating' => ['required', 'integer', 'between:1,5'],
            'price_transparency_rating' => ['required', 'integer', 'between:1,5'],
            'body' => ['required', 'string', 'min:30', 'max:3000'],
            'is_anonymous' => ['nullable', 'boolean'],
        ];
    }
}
