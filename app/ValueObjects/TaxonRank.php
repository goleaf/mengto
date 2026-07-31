<?php

declare(strict_types=1);

namespace App\ValueObjects;

use InvalidArgumentException;
use Stringable;

final readonly class TaxonRank implements Stringable
{
    public string $value;

    public function __construct(string $value)
    {
        $normalized = strtolower(trim($value));

        if ($normalized === '' || preg_match('/^[a-z][a-z0-9 -]{0,78}[a-z0-9]$/', $normalized) !== 1) {
            throw new InvalidArgumentException('Taxon rank must be a non-empty normalized source rank.');
        }

        $this->value = $normalized;
    }

    public static function fromSource(string $value): self
    {
        return new self(str_replace(['_', '-'], ' ', $value));
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
