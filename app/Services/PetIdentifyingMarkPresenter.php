<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\PetIdentifyingMarkVisibility;
use App\Models\PetProfile;
use App\Models\PetProfileIdentifyingMark;
use LogicException;

final class PetIdentifyingMarkPresenter
{
    /** @return list<array{key: string, type: string, description: string}> */
    public function publicFor(PetProfile $profile): array
    {
        if (! $profile->relationLoaded('activeIdentifyingMarks')) {
            throw new LogicException(__('pet_profiles.validation.identifying_marks_not_loaded'));
        }

        return $profile->activeIdentifyingMarks
            ->filter(static fn (PetProfileIdentifyingMark $mark): bool => $mark->visibility === PetIdentifyingMarkVisibility::Public)
            ->map(static fn (PetProfileIdentifyingMark $mark): array => [
                'key' => $mark->mark_key,
                'type' => $mark->type->label(),
                'description' => $mark->description,
            ])
            ->values()
            ->all();
    }
}
