<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\PetProfileNameType;
use App\Enums\PetProfileNameVisibility;
use App\Models\PetProfile;
use App\Models\PetProfileName;
use App\Models\User;

final readonly class PetProfileNameHistory
{
    public function __construct(private PetProfileNameNormalizer $normalizer) {}

    public function rememberPrevious(PetProfile $profile, User $actor, string $name): PetProfileName
    {
        $normalized = $this->normalizer->normalize($name);
        $existing = $profile->names()
            ->withTrashed()
            ->where('normalized_name', $normalized)
            ->first();

        if ($existing instanceof PetProfileName) {
            if ($existing->trashed()) {
                $existing->restore();
            }

            return $existing;
        }

        return $profile->names()->create([
            'name' => trim($name),
            'normalized_name' => $normalized,
            'type' => PetProfileNameType::Previous,
            'visibility' => PetProfileNameVisibility::Private,
            'locale' => null,
            'is_searchable' => true,
            'recorded_by_user_id' => $actor->id,
            'recorded_at' => now(),
        ]);
    }
}
