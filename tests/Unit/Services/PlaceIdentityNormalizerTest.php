<?php

declare(strict_types=1);

use App\Services\PlaceIdentityNormalizer;

test('place identity normalization produces stable duplicate signals', function () {
    $normalizer = new PlaceIdentityNormalizer;

    expect($normalizer->name('  Žvėryno PET–Clinic!  '))->toBe('zveryno pet clinic')
        ->and($normalizer->address(' Gedimino pr. 12,  LT–01103 Vilnius '))->toBe('gedimino pr 12 lt 01103 vilnius')
        ->and($normalizer->phone('+370 (612) 34-567'))->toBe('37061234567')
        ->and($normalizer->email(' INFO@Example.LT '))->toBe('info@example.lt')
        ->and($normalizer->website('HTTPS://WWW.Example.LT/places/?utm_source=test#hours'))
        ->toBe('example.lt/places');
});

test('place identity normalization calculates deterministic coordinate distance', function () {
    $normalizer = new PlaceIdentityNormalizer;

    expect($normalizer->distanceMeters('54.687200', '25.279700', '54.687200', '25.279700'))
        ->toBe(0)
        ->and($normalizer->distanceMeters('54.687200', '25.279700', '54.687650', '25.279700'))
        ->toBeGreaterThanOrEqual(49)
        ->toBeLessThanOrEqual(51)
        ->and($normalizer->distanceMeters(null, null, '54.687650', '25.279700'))
        ->toBeNull();
});
