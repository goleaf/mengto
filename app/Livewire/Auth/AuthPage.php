<?php

declare(strict_types=1);

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\App;
use Livewire\Component;

abstract class AuthPage extends Component
{
    /**
     * @return array{
     *     title: string,
     *     htmlLocale: string,
     *     activeLocale: string,
     *     supportedLocales: array<string, string>
     * }
     */
    final protected function authLayoutData(string $title): array
    {
        $activeLocale = App::currentLocale();
        $supportedLocales = request()->user() === null
            ? collect(config('platform.supported_locales', ['en']))
                ->mapWithKeys(fn (string $locale): array => [
                    $locale => __('auth.locales.'.$locale),
                ])
                ->all()
            : [];

        return [
            'title' => $title,
            'htmlLocale' => str_replace('_', '-', $activeLocale),
            'activeLocale' => $activeLocale,
            'supportedLocales' => $supportedLocales,
        ];
    }
}
