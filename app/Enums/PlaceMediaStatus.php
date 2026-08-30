<?php

declare(strict_types=1);

namespace App\Enums;

enum PlaceMediaStatus: string
{
    case PendingReview = 'pending_review';
    case Active = 'active';
    case Rejected = 'rejected';
    case Archived = 'archived';
    case Removed = 'removed';

    public function label(): string
    {
        return __('places.media.statuses.'.$this->value);
    }
}
