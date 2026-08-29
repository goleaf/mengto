<?php

declare(strict_types=1);

namespace App\ValueObjects;

use InvalidArgumentException;

final readonly class MinorUnitAmount
{
    private function __construct(private int $minorUnits) {}

    public static function fromDecimal(string|int $amount): self
    {
        $value = trim((string) $amount);

        if (preg_match('/^\d+(?:\.\d{1,2})?$/', $value) !== 1) {
            throw new InvalidArgumentException('Money must be a non-negative decimal with at most two fractional digits.');
        }

        [$major, $fraction] = array_pad(explode('.', $value, 2), 2, '');
        $major = ltrim($major, '0') ?: '0';
        $minorUnits = ltrim($major.str_pad($fraction, 2, '0'), '0') ?: '0';
        $maximum = (string) PHP_INT_MAX;

        if (strlen($minorUnits) > strlen($maximum)
            || (strlen($minorUnits) === strlen($maximum) && strcmp($minorUnits, $maximum) > 0)
        ) {
            throw new InvalidArgumentException('Money exceeds the supported integer range.');
        }

        return new self((int) $minorUnits);
    }

    public function add(self $amount): self
    {
        if ($this->minorUnits > PHP_INT_MAX - $amount->minorUnits) {
            throw new InvalidArgumentException('Money addition exceeds the supported integer range.');
        }

        return new self($this->minorUnits + $amount->minorUnits);
    }

    public function multiply(int $factor): self
    {
        if ($factor < 0 || ($factor > 0 && $this->minorUnits > intdiv(PHP_INT_MAX, $factor))) {
            throw new InvalidArgumentException('Money multiplication exceeds the supported integer range.');
        }

        return new self($this->minorUnits * $factor);
    }

    public function isPositive(): bool
    {
        return $this->minorUnits > 0;
    }

    public function isGreaterThan(self $amount): bool
    {
        return $this->minorUnits > $amount->minorUnits;
    }

    public function toDecimal(): string
    {
        return intdiv($this->minorUnits, 100).'.'.str_pad(
            (string) ($this->minorUnits % 100),
            2,
            '0',
            STR_PAD_LEFT,
        );
    }
}
