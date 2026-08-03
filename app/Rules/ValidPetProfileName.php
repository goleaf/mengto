<?php

declare(strict_types=1);

namespace App\Rules;

use App\Services\PetProfileNameNormalizer;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

final readonly class ValidPetProfileName implements ValidationRule
{
    private const RESERVED_NAMES = [
        'admin',
        'administrator',
        'moderator',
        'official',
        'pawcircle',
        'support',
        'system',
    ];

    public function __construct(private PetProfileNameNormalizer $normalizer) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value)
            || preg_match('/[\p{C}]/u', $value) === 1
            || preg_match('/[\p{L}\p{N}]/u', $value) !== 1
            || preg_match('/^[\p{L}\p{M}\p{N}\s.\'’\-()]+$/u', $value) !== 1
            || preg_match('/(.)\1{5,}/u', $value) === 1) {
            $fail('pet_profiles.validation.name_format')->translate();

            return;
        }

        if (in_array($this->normalizer->normalize($value), self::RESERVED_NAMES, true)) {
            $fail('pet_profiles.validation.name_reserved')->translate();
        }
    }
}
