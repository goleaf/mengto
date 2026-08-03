<?php

declare(strict_types=1);

namespace App\Enums;

enum PlaceType: string
{
    case PublicSpace = 'public_space';
    case Park = 'park';
    case WalkingRoute = 'walking_route';
    case VeterinaryClinic = 'veterinary_clinic';
    case Shelter = 'shelter';
    case OrganizationLocation = 'organization_location';
    case PrivateHome = 'private_home';
    case FosterLocation = 'foster_location';
    case PrivateTrainingSpace = 'private_training_space';
    case TemporaryMeetingPoint = 'temporary_meeting_point';

    public function label(): string
    {
        return __('places.types.'.$this->value);
    }
}
