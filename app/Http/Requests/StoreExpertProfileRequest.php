<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\CredentialType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreExpertProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'public_name' => ['required', 'string', 'min:2', 'max:120'],
            'legal_name' => ['nullable', 'string', 'max:160'],
            'primary_type' => ['required', 'string', 'max:80'],
            'headline' => ['required', 'string', 'min:12', 'max:180'],
            'bio' => ['required', 'string', 'min:80', 'max:5000'],
            'approach' => ['nullable', 'string', 'max:3000'],
            'boundaries' => ['required', 'string', 'min:20', 'max:3000'],
            'years_experience' => ['required', 'integer', 'min:0', 'max:80'],
            'country' => ['required', 'string', 'max:80'],
            'city' => ['required', 'string', 'max:100'],
            'service_area' => ['nullable', 'string', 'max:160'],
            'specializations' => ['required', 'array', 'min:1', 'max:12'],
            'specializations.*' => ['string', 'max:80', 'distinct'],
            'species' => ['required', 'array', 'min:1', 'max:12'],
            'species.*' => ['string', 'max:80', 'distinct'],
            'age_groups' => ['nullable', 'array', 'max:6'],
            'age_groups.*' => ['string', 'max:60', 'distinct'],
            'languages' => ['required', 'array', 'min:1', 'max:8'],
            'languages.*' => ['string', 'max:80', 'distinct'],
            'formats' => ['required', 'array', 'min:1', 'max:8'],
            'formats.*' => ['string', 'max:60', 'distinct'],
            'methods' => ['nullable', 'array', 'max:12'],
            'methods.*' => ['nullable', 'string', 'max:120', 'distinct'],
            'accessibility' => ['nullable', 'array', 'max:12'],
            'accessibility.*' => ['string', 'max:80', 'distinct'],
            'availability_status' => ['required', Rule::in(['available', 'limited', 'waitlist', 'unavailable'])],
            'response_time' => ['nullable', 'string', 'max:80'],
            'accepts_new_clients' => ['required', 'boolean'],
            'offers_emergency_care' => ['required', 'boolean'],
            'price_from' => ['nullable', 'numeric', 'min:0', 'max:100000'],
            'currency' => ['required', 'string', 'size:3'],
            'avatar_url' => ['nullable', 'url:http,https', 'max:2048'],
            'cover_url' => ['nullable', 'url:http,https', 'max:2048'],
            'credential_type' => ['nullable', Rule::enum(CredentialType::class)],
            'credential_title' => ['nullable', 'string', 'max:180'],
            'credential_issuer' => ['nullable', 'string', 'max:180'],
            'credential_file' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:10240'],
        ];
    }
}
