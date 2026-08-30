<?php

declare(strict_types=1);

namespace App\Enums;

enum OnboardingStep: string
{
    case Introduction = 'introduction';
    case Preferences = 'preferences';
    case PetRelationship = 'pet-relationship';
    case PrivacyDiscovery = 'privacy-discovery';
    case Complete = 'complete';

    public function next(): self
    {
        return match ($this) {
            self::Introduction => self::Preferences,
            self::Preferences => self::PetRelationship,
            self::PetRelationship => self::PrivacyDiscovery,
            self::PrivacyDiscovery, self::Complete => self::Complete,
        };
    }

    public function position(): int
    {
        return match ($this) {
            self::Introduction => 1,
            self::Preferences => 2,
            self::PetRelationship => 3,
            self::PrivacyDiscovery => 4,
            self::Complete => 5,
        };
    }
}
