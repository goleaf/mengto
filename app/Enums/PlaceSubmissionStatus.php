<?php

declare(strict_types=1);

namespace App\Enums;

enum PlaceSubmissionStatus: string
{
    case Submitted = 'submitted';
    case NeedsInformation = 'needs-information';
    case DuplicateReview = 'duplicate-review';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Published = 'published';
    case Withdrawn = 'withdrawn';

    public function isOpen(): bool
    {
        return in_array($this, [
            self::Submitted,
            self::DuplicateReview,
            self::NeedsInformation,
        ], true);
    }

    public function label(): string
    {
        return __('places.submissions.statuses.'.$this->value);
    }
}
