<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\DiscoveryCategory;
use App\Enums\DiscoveryPreferenceScope;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateDiscoveryPreferenceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isActive() === true;
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return [
            'action' => ['required', Rule::in(['hide', 'reset'])],
            'scope' => [
                'exclude_if:action,reset',
                'required',
                Rule::enum(DiscoveryPreferenceScope::class),
            ],
            'category' => [
                'exclude_if:action,reset',
                'required',
                Rule::in(DiscoveryCategory::recommendationValues()),
            ],
            'target_key' => [
                'exclude_if:action,reset',
                'required_if:scope,item',
                'nullable',
                'string',
                'max:160',
                'regex:/^[A-Za-z0-9._:-]+$/',
            ],
            'reason_code' => [
                'exclude_if:action,reset',
                'nullable',
                Rule::in(['not_relevant', 'not_interested', 'too_far', 'already_known']),
            ],
            'return_q' => ['nullable', 'string', 'max:80'],
            'return_category' => ['nullable', Rule::enum(DiscoveryCategory::class)],
        ];
    }
}
