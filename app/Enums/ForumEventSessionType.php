<?php

declare(strict_types=1);

namespace App\Enums;

enum ForumEventSessionType: string
{
    case Session = 'session';
    case Registration = 'registration';
    case DoorsOpen = 'doors_open';
    case Break = 'break';
    case AnimalRest = 'animal_rest';
    case WelfareCheck = 'welfare_check';
    case Meal = 'meal';
    case Networking = 'networking';
    case CompetitionCategory = 'competition_category';
    case Awards = 'awards';
    case VendorHours = 'vendor_hours';

    public function label(): string
    {
        return __('forum_events.session_types.'.$this->value);
    }
}
