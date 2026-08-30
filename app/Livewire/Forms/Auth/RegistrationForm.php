<?php

declare(strict_types=1);

namespace App\Livewire\Forms\Auth;

use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Livewire\Form;

final class RegistrationForm extends Form
{
    public string $name = '';

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    /**
     * @return array<string, array<int, mixed>>
     */
    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:120'],
            'email' => [
                'required',
                'string',
                'email:rfc',
                'max:254',
                Rule::unique('users', 'email'),
            ],
            'password' => [
                'required',
                'string',
                'confirmed',
                Password::min(12)->mixedCase()->numbers(),
            ],
        ];
    }

    /**
     * @return array{name: string, email: string, password: string}
     */
    public function validatedData(): array
    {
        $this->name = trim($this->name);
        $this->email = Str::lower(trim($this->email));

        /** @var array{name: string, email: string, password: string} $validated */
        $validated = $this->validate();

        return $validated;
    }
}
