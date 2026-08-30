<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class BrowsePlacesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'q' => ['nullable', 'string', 'max:120'],
            'area' => ['nullable', 'string', 'max:80'],
            'category' => [
                'nullable',
                Rule::in([
                    'all',
                    'park',
                    'dog-park',
                    'route',
                    'vet',
                    'emergency-vet',
                    'pet-store',
                    'grooming',
                    'shelter',
                    'pet-cafe',
                ]),
            ],
            'species' => ['nullable', Rule::in(['any', 'dog', 'cat', 'bird', 'rabbit', 'rodent', 'reptile', 'exotic'])],
            'size' => ['nullable', Rule::in(['any', 'very-small', 'small', 'medium', 'large', 'very-large'])],
            'distance' => ['nullable', Rule::in(['any', '1', '5', '10'])],
            'open_now' => ['nullable', 'boolean'],
            'leash' => ['nullable', Rule::in(['any', 'off-leash', 'fenced', 'required'])],
            'accessibility' => ['nullable', Rule::in(['any', 'wheelchair', 'quiet', 'parking', 'lighting'])],
            'safety' => ['nullable', Rule::in(['any', 'fenced', 'water', 'lighting', 'no-warnings'])],
            'price' => ['nullable', Rule::in(['any', 'free', 'paid'])],
            'rating' => ['nullable', Rule::in(['any', '4', '4.5'])],
            'verification' => ['nullable', Rule::in(['any', 'verified', 'community', 'recent'])],
            'crowd' => ['nullable', Rule::in(['any', 'low', 'medium', 'high', 'unknown'])],
            'visit_time' => ['nullable', Rule::in(['any', 'morning', 'evening', 'night', 'quiet'])],
            'pet' => ['nullable', Rule::in(['scout', 'nori', 'none'])],
            'sort' => ['nullable', Rule::in(['recommended', 'distance', 'travel-time', 'rating', 'reviews', 'open', 'freshness', 'name'])],
            'view' => ['nullable', Rule::in(['split', 'map', 'list', 'fullscreen', 'route'])],
            'mode' => ['nullable', Rule::in(['browse', 'favorites', 'visited', 'events', 'warnings', 'emergency'])],
            'layer' => ['nullable', Rule::in(['places', 'routes', 'events', 'warnings', 'lost-pets', 'emergency'])],
            'selected' => ['nullable', 'string', 'max:80', 'regex:/^[a-z0-9-]+$/'],
            'emergency' => ['nullable', 'boolean'],
            'page' => ['nullable', 'integer', 'min:1', 'max:10000'],
            'tab' => [
                'nullable',
                Rule::in([
                    'overview',
                    'photos',
                    'services',
                    'rules',
                    'hours',
                    'specialists',
                    'reviews',
                    'events',
                    'questions',
                    'map',
                    'updates',
                    'corrections',
                ]),
            ],
        ];
    }
}
