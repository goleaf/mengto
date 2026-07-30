<?php

declare(strict_types=1);

use App\Services\LocaleFormatter;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\App;

test('locale formatter applies the account locale and timezone', function () {
    $instant = CarbonImmutable::parse('2026-01-02T23:30:00Z');
    $formatter = app(LocaleFormatter::class);

    App::setLocale('en');
    $this->authenticatedUser->update(['timezone' => 'Europe/Vilnius']);

    expect($formatter->dateTime($instant))
        ->toContain('Jan')
        ->toContain('2026')
        ->and($formatter->number(1234.5, 1))
        ->toBe('1,234.5')
        ->and($formatter->currency(12.5, 'EUR'))
        ->toContain('12.50')
        ->and($formatter->list(['GPS', 'camera', 'door']))
        ->toBe('GPS, camera, and door');
});

test('locale formatter changes punctuation and language with locale', function () {
    $instant = CarbonImmutable::parse('2026-01-02T12:00:00Z');

    App::setLocale('ru');

    $formatter = app(LocaleFormatter::class);

    expect($formatter->date($instant))
        ->toContain('2026')
        ->and($formatter->number(1234.5, 1))
        ->toContain('1 234,5')
        ->and($formatter->list(['GPS', 'камера', 'дверь']))
        ->toBe('GPS, камера и дверь');
});
