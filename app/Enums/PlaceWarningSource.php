<?php

declare(strict_types=1);

namespace App\Enums;

enum PlaceWarningSource: string
{
    case Community = 'community';
    case EmergencyService = 'emergency_service';
    case Manager = 'manager';
    case Official = 'official';
    case PersonalObservation = 'personal_observation';
    case Other = 'other';
    case LegacyImport = 'legacy_import';
}
