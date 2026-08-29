<?php

declare(strict_types=1);

use App\ValueObjects\MinorUnitAmount;

test('decimal amounts multiply and add without floating point arithmetic', function () {
    $unit = MinorUnitAmount::fromDecimal('12.37');
    $deposit = MinorUnitAmount::fromDecimal(20);

    expect($unit->multiply(3)->multiply(2)->add($deposit)->toDecimal())
        ->toBe('94.22')
        ->and(MinorUnitAmount::fromDecimal('0.29')->multiply(3)->toDecimal())
        ->toBe('0.87');
});

test('decimal amounts reject unsupported precision and negative values', function (string $amount) {
    MinorUnitAmount::fromDecimal($amount);
})->with(['1.001', '-1.00', 'not-money'])->throws(InvalidArgumentException::class);

test('decimal amounts reject arithmetic overflow', function () {
    $maximum = MinorUnitAmount::fromDecimal('92233720368547758.07');

    expect($maximum->toDecimal())->toBe('92233720368547758.07');

    $maximum->add(MinorUnitAmount::fromDecimal('0.01'));
})->throws(InvalidArgumentException::class);

test('decimal amount comparison treats equality as not greater', function () {
    $amount = MinorUnitAmount::fromDecimal('10.00');

    expect($amount->isGreaterThan(MinorUnitAmount::fromDecimal('10.00')))->toBeFalse()
        ->and($amount->isGreaterThan(MinorUnitAmount::fromDecimal('9.99')))->toBeTrue();
});

test('decimal amounts reject negative or overflowing multiplication factors', function (int $factor) {
    MinorUnitAmount::fromDecimal('92233720368547758.07')->multiply($factor);
})->with([-1, 2])->throws(InvalidArgumentException::class);
