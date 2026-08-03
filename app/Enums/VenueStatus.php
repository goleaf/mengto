<?php

declare(strict_types=1);

namespace App\Enums;

enum VenueStatus: string
{
    case Active = 'active';
    case PendingConfirmation = 'pending_confirmation';
    case Suspended = 'suspended';
    case Archived = 'archived';

    public function label(): string
    {
        return __('places.venue_statuses.'.$this->value);
    }
}
