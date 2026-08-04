<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\PetSizeCategory;
use Illuminate\Validation\ValidationException;

final class PetSizeCategoryNormalizer
{
    public function normalize(mixed $value): ?PetSizeCategory
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (! is_string($value)) {
            $this->invalid();
        }

        $category = PetSizeCategory::tryFrom($value);

        if (! $category instanceof PetSizeCategory) {
            $this->invalid();
        }

        return $category;
    }

    private function invalid(): never
    {
        throw ValidationException::withMessages([
            'size_category' => __('pet_profiles.validation.size_category'),
        ]);
    }
}
