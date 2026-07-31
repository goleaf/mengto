<?php

declare(strict_types=1);

namespace App\Enums;

enum AdoptionCaseStatus: string
{
    case Draft = 'draft';
    case PendingReview = 'pending-review';
    case Published = 'published';
    case Screening = 'screening';
    case Reserved = 'reserved';
    case Trial = 'trial';
    case Adopted = 'adopted';
    case Fostered = 'fostered';
    case Returned = 'returned';
    case Failed = 'failed';
    case Closed = 'closed';
    case Archived = 'archived';

    public function label(): string
    {
        return __("adoption.case_status.{$this->value}");
    }

    public function acceptsApplications(): bool
    {
        return in_array($this, [
            self::Published,
            self::Screening,
            self::Returned,
        ], true);
    }
}
