<?php

declare(strict_types=1);

namespace App\Enums;

enum PlaceAccessibilityStatus: string
{
    case Confirmed = 'confirmed';
    case PartiallyAccessible = 'partially_accessible';
    case VenueSupplied = 'venue_supplied';
    case AccommodationOnRequest = 'accommodation_on_request';
    case NotAssessed = 'not_assessed';
    case Inaccessible = 'inaccessible';

    public function label(): string
    {
        return __('places.accessibility_statuses.'.$this->value);
    }
}
