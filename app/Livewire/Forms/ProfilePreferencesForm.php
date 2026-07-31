<?php

declare(strict_types=1);

namespace App\Livewire\Forms;

use App\Models\User;
use Illuminate\Validation\Rule;
use Livewire\Form;

final class ProfilePreferencesForm extends Form
{
    public string $locale = 'en';

    public string $timezone = 'UTC';

    /**
     * @return array<string, array<int, mixed>>
     */
    protected function rules(): array
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

    /**
     * @return array<string, string>
     */
    protected function validationAttributes(): array
    {
        return [
            'locale' => __('auth.fields.locale'),
            'timezone' => __('auth.fields.timezone'),
        ];
    }

    public function fillFromUser(User $user): void
    {
        $this->locale = $user->locale;
        $this->timezone = $user->timezone;
    }

    /**
     * @return array{locale: string, timezone: string}
     */
    public function validatedData(): array
    {
        /** @var array{locale: string, timezone: string} $validated */
        $validated = $this->validate();

        return $validated;
    }
}
