<?php

declare(strict_types=1);

namespace App\Enums;

enum PlaceStatus: string
{
    case Active = 'active';
    case TemporarilyClosed = 'temporarily_closed';
    case Suspended = 'suspended';
    case Archived = 'archived';
    case Merged = 'merged';

    public function label(): string
    {
        return __('places.statuses.'.$this->value);
    }
}
