<?php

declare(strict_types=1);

namespace App\Enums;

enum PlaceSubmissionSource: string
{
    case PersonalVisit = 'personal_visit';
    case OfficialWebsite = 'official_website';
    case Organization = 'organization';
    case PublicRegistry = 'public_registry';
    case CommunityReport = 'community_report';
    case Other = 'other';

    public function label(): string
    {
        return __('places.submissions.sources.'.$this->value);
    }
}
