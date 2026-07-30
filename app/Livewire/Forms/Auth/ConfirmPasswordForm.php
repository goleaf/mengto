<?php

declare(strict_types=1);

namespace App\Livewire\Forms\Auth;

use Livewire\Form;

final class ConfirmPasswordForm extends Form
{
    public string $password = '';

    /**
     * @return array<string, list<string>>
     */
    protected function rules(): array
    {
        return [
            'password' => ['required', 'string', 'max:4096'],
        ];
    }
}
