<?php

declare(strict_types=1);

namespace App\Enums;

enum PetBreedConfidence: string
{
    case Confirmed = 'confirmed';
    case OwnerReported = 'owner-reported';
    case Suspected = 'suspected';

    public function label(): string
    {
        return __("pet_profiles.breed_origin.confidences.{$this->value}");
    }
}
