<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\PetLifeStage;
use App\Models\PetProfile;
use DateTimeInterface;

final readonly class PetLifeStagePresenter
{
    private const SPECIES_LABELS = [
        'dog',
        'cat',
        'bird',
        'reptile',
        'horse',
    ];

    public function __construct(private PetLifeStageResolver $resolver) {}

    /** @return array{stage: string, label: string, source: string, source_label: string} */
    public function for(PetProfile $profile, ?DateTimeInterface $at = null): array
    {
        $stage = $this->resolver->for($profile, $at);
        $manual = $profile->life_stage_override instanceof PetLifeStage;
        $source = $manual
            ? 'manual'
            : ($stage === PetLifeStage::Unknown ? 'unavailable' : 'automatic');

        return [
            'stage' => $stage->value,
            'label' => $this->label($profile->species, $stage),
            'source' => $source,
            'source_label' => __("pet_profiles.life_stage.sources.{$source}"),
        ];
    }

    private function label(string $species, PetLifeStage $stage): string
    {
        if ($stage !== PetLifeStage::Unknown
            && in_array($species, self::SPECIES_LABELS, true)) {
            return __("pet_profiles.life_stage.species.{$species}.{$stage->value}");
        }

        return __("pet_profiles.life_stage.stages.{$stage->value}");
    }
}
