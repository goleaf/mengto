<?php

declare(strict_types=1);

namespace App\Enums;

enum VenueAreaType: string
{
    case Registration = 'registration';
    case MainHall = 'main_hall';
    case TrainingRoom = 'training_room';
    case CompetitionArea = 'competition_area';
    case QuietArea = 'quiet_area';
    case AnimalRestArea = 'animal_rest_area';
    case IsolationArea = 'isolation_area';
    case MedicalArea = 'medical_area';
    case VendorArea = 'vendor_area';
    case StaffOnly = 'staff_only';
    case RestrictedMedia = 'restricted_media';

    public function label(): string
    {
        return __('places.venue_area_types.'.$this->value);
    }
}
