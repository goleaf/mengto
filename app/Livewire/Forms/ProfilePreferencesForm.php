<?php

declare(strict_types=1);

namespace App\Livewire\Forms;

use App\Models\User;
use App\Validation\ProfilePreferenceRules;
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
        return ProfilePreferenceRules::rules();
    }

    /**
     * @return array<string, string>
     */
    protected function validationAttributes(): array
    {
        return ProfilePreferenceRules::attributes();
    }

    /** @return array<string, string> */
    protected function messages(): array
    {
        return ProfilePreferenceRules::messages();
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
