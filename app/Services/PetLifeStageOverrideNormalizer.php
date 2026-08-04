<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\PetLifeStage;
use App\Models\PetProfile;
use Carbon\CarbonImmutable;
use Illuminate\Validation\ValidationException;

final class PetLifeStageOverrideNormalizer
{
    public function normalize(mixed $value): ?PetLifeStage
    {
        if ($value === null || $value === '' || $value === 'auto') {
            return null;
        }

        $stage = $value instanceof PetLifeStage
            ? $value
            : PetLifeStage::tryFrom((string) $value);

        if (! $stage instanceof PetLifeStage) {
            throw ValidationException::withMessages([
                'life_stage_override' => __('pet_profiles.validation.life_stage'),
            ]);
        }

        return $stage;
    }

    /**
     * @return array{
     *     life_stage_override: PetLifeStage|null,
     *     life_stage_override_by_user_id: int|null,
     *     life_stage_override_at: CarbonImmutable|null
     * }
     */
    public function attributes(PetProfile $profile, mixed $value, int $actorId): array
    {
        $stage = $this->normalize($value);

        if ($profile->life_stage_override === $stage) {
            return [
                'life_stage_override' => $profile->life_stage_override,
                'life_stage_override_by_user_id' => $profile->life_stage_override_by_user_id,
                'life_stage_override_at' => $profile->life_stage_override_at,
            ];
        }

        return [
            'life_stage_override' => $stage,
            'life_stage_override_by_user_id' => $stage === null ? null : $actorId,
            'life_stage_override_at' => $stage === null ? null : now()->toImmutable(),
        ];
    }
}
