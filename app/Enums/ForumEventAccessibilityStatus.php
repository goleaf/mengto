<?php

declare(strict_types=1);

namespace App\Enums;

enum ForumEventAccessibilityStatus: string
{
    case Confirmed = 'confirmed';
    case Partial = 'partial';
    case VenueSupplied = 'venue_supplied';
    case AccommodationOnRequest = 'accommodation_on_request';
    case NotAssessed = 'not_assessed';
    case Inaccessible = 'inaccessible';

    public function label(): string
    {
        return __('forum_events.accessibility_statuses.'.$this->value);
    }
}
