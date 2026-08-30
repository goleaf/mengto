<?php

declare(strict_types=1);

namespace App\Enums;

enum PlaceCorrectionSource: string
{
    case PersonalObservation = 'personal_observation';
    case PlaceManager = 'place_manager';
    case PublicSource = 'public_source';
    case OfficialSource = 'official_source';
    case Other = 'other';
    case LegacyImport = 'legacy_import';
}
