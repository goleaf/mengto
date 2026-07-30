<?php

namespace App\Enums;

enum ReviewStatus: string
{
    case Published = 'published';
    case InReview = 'in-review';
    case Hidden = 'hidden';
    case Removed = 'removed';

    public function label(): string
    {
        return str($this->value)->headline()->toString();
    }
}
