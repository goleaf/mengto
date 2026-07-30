<?php

namespace App\Http\Requests;

use App\Services\SearchSafety;
use App\Services\SearchTaxonomy;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreSearchCaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(SearchTaxonomy $taxonomy): array
    {
        return [
            'type' => ['required', Rule::in(array_keys($taxonomy->types()))],
            'intent' => ['required', Rule::in(['publish', 'draft'])],
            'pet_profile_key' => ['nullable', 'string', 'max:80', 'regex:/^[a-z0-9-]+$/'],
            'pet_name' => ['required', 'string', 'max:100'],
            'species' => ['required', Rule::in(array_keys($taxonomy->species()))],
            'breed' => ['nullable', 'string', 'max:120'],
            'sex' => ['nullable', Rule::in(['male', 'female', 'unknown'])],
            'age_label' => ['nullable', 'string', 'max:80'],
            'size' => ['nullable', Rule::in(array_keys($taxonomy->sizes()))],
            'primary_color' => ['required', 'string', 'max:80'],
            'coat' => ['nullable', 'string', 'max:80'],
            'distinctive_marks' => ['nullable', 'string', 'max:1500'],
            'hidden_marks' => ['nullable', 'string', 'max:1000'],
            'description' => ['required', 'string', 'min:20', 'max:4000'],
            'health_notice' => ['nullable', 'string', 'max:1000'],
            'approach_instructions' => ['nullable', 'string', 'max:1500'],
            'avoid_instructions' => ['nullable', 'string', 'max:1500'],
            'accessories' => ['nullable', 'array', 'max:8'],
            'accessories.*' => ['string', 'max:80'],
            'microchip_status' => ['required', Rule::in(array_keys($taxonomy->microchipStatuses()))],
            'last_seen_area' => ['required', 'string', 'max:160'],
            'city' => ['required', 'string', 'max:100'],
            'country' => ['required', 'string', 'size:2'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'location_note' => ['nullable', 'string', 'max:300'],
            'direction' => ['nullable', 'string', 'max:100'],
            'last_seen_at' => ['required', 'date', 'before_or_equal:now'],
            'notification_radius_km' => ['required', 'integer', 'min:1', 'max:100'],
            'visibility' => ['required', Rule::in(['public', 'registered', 'local-group', 'link'])],
            'animal_secured' => ['nullable', 'boolean'],
            'contact_channel' => ['required', Rule::in(['platform', 'email', 'phone'])],
            'contact_value' => ['nullable', 'string', 'max:160'],
            'cover_url' => ['nullable', 'url:http,https', 'max:2048'],
            'photos' => ['nullable', 'array', 'max:8'],
            'photos.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
            'safety_acknowledged' => ['required', 'accepted'],
        ];
    }

    public function after(SearchSafety $safety): array
    {
        return [
            function (Validator $validator) use ($safety): void {
                if ($this->string('type')->toString() === 'lost' && blank($this->input('pet_profile_key'))) {
                    $validator->errors()->add('pet_profile_key', 'Choose the pet profile for a missing-pet search.');
                }

                if ($this->string('contact_channel')->toString() !== 'platform' && blank($this->input('contact_value'))) {
                    $validator->errors()->add('contact_value', 'Add the protected contact value.');
                }

                $assessment = $safety->assessCase($this->all());
                foreach ($assessment['flags'] as $flag) {
                    if (in_array($flag, ['sensitive-payment-data', 'threat-language'], true)) {
                        $validator->errors()->add(
                            'description',
                            'Remove payment codes, threats, or other unsafe details before publishing.',
                        );
                    }
                }
            },
        ];
    }
}
