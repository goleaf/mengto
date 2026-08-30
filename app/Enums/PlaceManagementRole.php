<?php

declare(strict_types=1);

namespace App\Enums;

enum PlaceManagementRole: string
{
    case Owner = 'owner';
    case OrganizationManager = 'organization_manager';
    case StaffManager = 'staff_manager';

    public function label(): string
    {
        return __('places.management.roles.'.$this->value);
    }
}
