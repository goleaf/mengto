<?php

declare(strict_types=1);

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

use function Pest\Laravel\get;

/**
 * @return array<string, mixed>
 */
function localeCatalogue(string $locale): array
{
    return collect(File::files(lang_path($locale)))
        ->filter(fn (SplFileInfo $file): bool => $file->getExtension() === 'php')
        ->mapWithKeys(fn (SplFileInfo $file): array => [
            $file->getFilenameWithoutExtension() => require $file->getPathname(),
        ])
        ->all();
}

/**
 * @return list<string>
 */
function translationPlaceholders(string $value): array
{
    preg_match_all('/:[A-Za-z_][A-Za-z0-9_]*/', $value, $matches);
    $placeholders = array_values(array_unique($matches[0]));
    sort($placeholders);

    return $placeholders;
}

test('all supported locales expose the same translation keys and placeholders', function () {
    $locales = config('platform.supported_locales');
    $catalogues = collect($locales)
        ->mapWithKeys(fn (string $locale): array => [$locale => localeCatalogue($locale)]);
    $source = Arr::dot($catalogues->get('en'));

    foreach ($catalogues as $locale => $catalogue) {
        $flattened = Arr::dot($catalogue);

        expect(array_keys($flattened))->toBe(array_keys($source));

        foreach ($source as $key => $sourceValue) {
            if (! is_string($sourceValue) || ! is_string($flattened[$key])) {
                continue;
            }

            expect(translationPlaceholders($flattened[$key]))
                ->toBe(translationPlaceholders($sourceValue));
        }
    }
});

test('critical authentication pages render in every supported locale', function (string $locale) {
    Auth::logout();
    Auth::forgetGuards();
    Session::put('locale', $locale);

    get(route('login'))
        ->assertOk()
        ->assertDontSee('auth.login.title');
})->with(['en', 'lt', 'ru']);

test('validation messages resolve in every supported locale', function (string $locale) {
    App::setLocale($locale);

    $message = Validator::make([], [
        'email' => ['required', 'email'],
    ])->errors()->first('email');

    expect(in_array($message, ['', 'validation.required'], true))->toBeFalse();
})->with(['en', 'lt', 'ru']);
