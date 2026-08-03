<?php

declare(strict_types=1);

namespace App\Enums;

enum PetBreedSource: string
{
    case Document = 'document';
    case Pedigree = 'pedigree';
    case Shelter = 'shelter';
    case Veterinarian = 'veterinarian';
    case GeneticTest = 'genetic-test';
    case OwnerAssumption = 'owner-assumption';
    case Unknown = 'unknown';

    public function label(): string
    {
        return __("pet_profiles.breed_origin.sources.{$this->value}");
    }
}
