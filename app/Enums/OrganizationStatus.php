<?php

declare(strict_types=1);

namespace App\Enums;

enum OrganizationStatus: string
{
    case Draft = 'draft';
    case Active = 'active';
    case Suspended = 'suspended';
    case Archived = 'archived';

    public function label(): string
    {
        return __('organizations.statuses.'.$this->value);
    }
}
