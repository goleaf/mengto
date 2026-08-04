<?php

declare(strict_types=1);

namespace App\Enums;

enum PetIdentifyingMarkType: string
{
    case Scar = 'scar';
    case Spot = 'spot';
    case EarFeature = 'ear-feature';
    case DifferentEyeColors = 'different-eye-colors';
    case ShortenedTail = 'shortened-tail';
    case PawFeature = 'paw-feature';
    case Tattoo = 'tattoo';
    case UnusualPattern = 'unusual-pattern';
    case Deformity = 'deformity';
    case OldInjuryEffect = 'old-injury-effect';

    public function label(): string
    {
        return __("pet_profiles.identifying_marks.types.{$this->value}");
    }
}
