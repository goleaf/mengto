<?php

declare(strict_types=1);

namespace App\Enums;

enum OnboardingPetChoice: string
{
    case ManagedPet = 'managed-pet';
    case AccessRequested = 'access-requested';
    case NotNow = 'not-now';
}
