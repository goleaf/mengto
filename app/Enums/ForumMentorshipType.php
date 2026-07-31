<?php

declare(strict_types=1);

namespace App\Enums;

enum ForumMentorshipType: string
{
    case FirstTimeOwner = 'first-time-owner';
    case NewSpeciesOwner = 'new-species-owner';
    case AdoptionAdaptation = 'adoption-adaptation';
    case FosterSupport = 'foster-support';
    case TrainingSupport = 'training-support';
    case SeniorAnimalCare = 'senior-animal-care';
    case SpecialNeedsCare = 'special-needs-care';
    case AquariumSetup = 'aquarium-setup';
    case TerrariumSetup = 'terrarium-setup';
    case HorseOwnership = 'horse-ownership';
    case FarmAnimalCare = 'farm-animal-care';
    case LostAnimalSearch = 'lost-animal-search';
    case VolunteerOnboarding = 'volunteer-onboarding';

    public function label(): string
    {
        return __("forum_mentorship.types.{$this->value}");
    }
}
