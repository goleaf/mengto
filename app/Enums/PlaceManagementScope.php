<?php

declare(strict_types=1);

namespace App\Enums;

enum PlaceManagementScope: string
{
    case Identity = 'identity';
    case PublicInformation = 'public_information';
    case Contacts = 'contacts';
    case Hours = 'hours';
    case Services = 'services';
    case Rules = 'rules';
    case Accessibility = 'accessibility';
    case Safety = 'safety';
    case PublicLocation = 'public_location';
    case ExactLocation = 'exact_location';
    case OfficialResponses = 'official_responses';
    case AccessManagement = 'access_management';

    public function label(): string
    {
        return __('places.management.scopes.'.$this->value);
    }
}
