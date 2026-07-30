<?php

namespace App\Http\Requests;

use App\Services\SearchTaxonomy;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSightingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        $taxonomy = app(SearchTaxonomy::class);

        return [
            'idempotency_key' => ['required', 'uuid'],
            'observed_at' => ['required', 'date', 'before_or_equal:now'],
            'time_accuracy' => [
                'required',
                Rule::in(['exact', 'within-30-minutes', 'morning', 'afternoon', 'evening', 'night', 'unknown']),
            ],
            'public_area' => ['required', 'string', 'max:160'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'location_note' => ['nullable', 'string', 'max:300'],
            'direction' => ['nullable', 'string', 'max:100'],
            'distance' => ['nullable', 'string', 'max:60'],
            'confidence' => ['required', Rule::in(array_keys($taxonomy->confidenceOptions()))],
            'contact_status' => ['required', Rule::in(array_keys($taxonomy->contactStatuses()))],
            'animal_condition' => ['nullable', 'string', 'max:120'],
            'danger' => ['nullable', 'string', 'max:120'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
            'video' => ['nullable', 'file', 'mimetypes:video/mp4,video/webm,video/quicktime', 'max:30720'],
            'is_anonymous' => ['nullable', 'boolean'],
            'safety_acknowledged' => ['required', 'accepted'],
        ];
    }
}
