<?php

declare(strict_types=1);

namespace App\Validation;

use Illuminate\Validation\Rule;

final class ProfilePreferenceRules
{
    /** @return array<string, list<mixed>> */
    public static function rules(): array
    {
        return [
            'locale' => [
                'required',
                'string',
                Rule::in(config('platform.supported_locales', ['en'])),
            ],
            'timezone' => ['required', 'string', 'timezone:all'],
        ];
    }

    /** @return array<string, string> */
    public static function messages(): array
    {
        return [
            'locale.required' => __('auth.settings.validation.locale'),
            'locale.string' => __('auth.settings.validation.locale'),
            'locale.in' => __('auth.settings.validation.locale'),
            'timezone.required' => __('auth.settings.validation.timezone'),
            'timezone.string' => __('auth.settings.validation.timezone'),
            'timezone.timezone' => __('auth.settings.validation.timezone'),
        ];
    }

    /** @return array<string, string> */
    public static function attributes(): array
    {
        return [
            'locale' => __('auth.fields.locale'),
            'timezone' => __('auth.fields.timezone'),
        ];
    }
}
