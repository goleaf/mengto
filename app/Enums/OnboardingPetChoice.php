<?php

declare(strict_types=1);

namespace App\Enums;

enum OnboardingPetChoice: string
{
    case ManagedPet = 'managed-pet';
    case AccessRequested = 'access-requested';
    case NoPet = 'no-pet';
    case AddLater = 'add-later';

    /**
     * Backward-compatible value written before the two truthful non-pet
     * decisions were separated. New presentation code does not offer it.
     */
    case NotNow = 'not-now';

    public function requiresPetEvidence(): bool
    {
        return in_array($this, [self::ManagedPet, self::AccessRequested], true);
    }

    /** @return list<self> */
    public static function selectableCases(): array
    {
        return [self::ManagedPet, self::AccessRequested, self::NoPet, self::AddLater];
    }
}
