<?php

declare(strict_types=1);

namespace App\Enums;

enum PlaceAccessPurpose: string
{
    case EventAttendance = 'event_attendance';
    case EventOperations = 'event_operations';
    case FosterCare = 'foster_care';
    case AdoptionMeeting = 'adoption_meeting';
    case ProfessionalVisit = 'professional_visit';
    case EmergencyResponse = 'emergency_response';

    public function label(): string
    {
        return __('places.access_purposes.'.$this->value);
    }
}
