<?php

declare(strict_types=1);

namespace App\Enums;

enum PlaceVerificationStatus: string
{
    case OrganizerProvided = 'organizer_provided';
    case VenueConfirmed = 'venue_confirmed';
    case OrganizationConfirmed = 'organization_confirmed';
    case Verified = 'verified';
    case NotAssessed = 'not_assessed';
    case Expired = 'expired';
    case Disputed = 'disputed';

    public function label(): string
    {
        return __('places.verification_statuses.'.$this->value);
    }
}
