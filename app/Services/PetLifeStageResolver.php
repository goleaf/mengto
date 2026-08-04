<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\PetLifeStage;
use App\Enums\PetSpeciesConfidence;
use App\Models\PetProfile;
use DateTimeInterface;

final readonly class PetLifeStageResolver
{
    public function __construct(private PetProfileAgeCalculator $ages) {}

    public function for(PetProfile $profile, ?DateTimeInterface $at = null): PetLifeStage
    {
        if ($profile->life_stage_override instanceof PetLifeStage) {
            return $profile->life_stage_override;
        }

        if ($profile->species_confidence !== PetSpeciesConfidence::Confirmed) {
            return PetLifeStage::Unknown;
        }

        $thresholds = config("pet_profiles.life_stage_threshold_months.{$profile->species}");
        $range = $this->ages->monthsRange($profile, $at);

        if (! is_array($thresholds) || $range === null) {
            return PetLifeStage::Unknown;
        }

        $minimum = $this->stageAt($range['minimum'], $thresholds);
        $maximum = $this->stageAt($range['maximum'], $thresholds);

        return $minimum === $maximum ? $minimum : PetLifeStage::Unknown;
    }

    /** @param array<string, mixed> $thresholds */
    private function stageAt(int $months, array $thresholds): PetLifeStage
    {
        $resolved = PetLifeStage::Unknown;

        foreach (PetLifeStage::derivedStages() as $stage) {
            $startsAt = $thresholds[$stage->value] ?? null;

            if (! is_int($startsAt) || $months < $startsAt) {
                continue;
            }

            $resolved = $stage;
        }

        return $resolved;
    }
}
