<?php

declare(strict_types=1);

namespace App\Enums;

enum ForumEventPetParticipation: string
{
    case HumansOnly = 'humans_only';
    case Optional = 'optional';
    case Required = 'required';
    case SelectedSpecies = 'selected_species';
    case ParticipatingAnimals = 'participating_animals';
    case VisitorAnimals = 'visitor_animals';
    case AssistanceAnimalsOnly = 'assistance_animals_only';
    case EnvironmentUnsuitable = 'environment_unsuitable';

    public function label(): string
    {
        return __('forum_events.pet_participation.'.$this->value);
    }

    public function acceptsGeneralPets(): bool
    {
        return in_array($this, [
            self::Optional,
            self::Required,
            self::SelectedSpecies,
            self::ParticipatingAnimals,
            self::VisitorAnimals,
        ], true);
    }
}
