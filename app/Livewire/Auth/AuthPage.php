<?php

declare(strict_types=1);

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\App;
use Livewire\Component;

abstract class AuthPage extends Component
{
    /**
     * @return array{title: string, htmlLocale: string}
     */
    final protected function authLayoutData(string $title): array
    {
        return [
            'title' => $title,
            'htmlLocale' => str_replace('_', '-', App::currentLocale()),
        ];
    }
}
