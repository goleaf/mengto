<?php

declare(strict_types=1);

namespace App\Enums;

enum PlaceServiceAccessMode: string
{
    case WalkIn = 'walk_in';
    case CallRequired = 'call_required';
    case AppointmentRequired = 'appointment_required';
    case Unknown = 'unknown';
}
