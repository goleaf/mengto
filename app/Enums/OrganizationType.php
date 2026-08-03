<?php

declare(strict_types=1);

namespace App\Enums;

enum OrganizationType: string
{
    case Community = 'community';
    case Shelter = 'shelter';
    case Rescue = 'rescue';
    case Professional = 'professional';
    case Venue = 'venue';
    case Marketplace = 'marketplace';
    case Platform = 'platform';

    public function label(): string
    {
        return __('organizations.types.'.$this->value);
    }
}
