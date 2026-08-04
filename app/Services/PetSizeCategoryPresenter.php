<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\PetSizeCategory;
use App\Models\PetProfile;

final class PetSizeCategoryPresenter
{
    /** @return array{value: string, label: string, description: string}|null */
    public function for(PetProfile $profile): ?array
    {
        $value = $profile->getRawOriginal('size_category');

        if (! is_string($value)) {
            return null;
        }

        $category = PetSizeCategory::tryFrom($value);

        if (! $category instanceof PetSizeCategory) {
            return null;
        }

        return [
            'value' => $category->value,
            'label' => $category->label(),
            'description' => $category->description(),
        ];
    }
}
