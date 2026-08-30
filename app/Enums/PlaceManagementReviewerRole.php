<?php

declare(strict_types=1);

namespace App\Enums;

enum PlaceManagementReviewerRole: string
{
    case Reviewer = 'reviewer';
    case Moderator = 'moderator';

    public function label(): string
    {
        return __('places.management.reviewer_roles.'.$this->value);
    }
}
