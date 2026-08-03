<?php

declare(strict_types=1);

namespace App\Enums;

enum ForumEventSessionReservationPolicy: string
{
    case Automatic = 'automatic';
    case Optional = 'optional';
    case Required = 'required';
    case Closed = 'closed';

    public function label(): string
    {
        return __('forum_events.session_reservation_policies.'.$this->value);
    }
}
