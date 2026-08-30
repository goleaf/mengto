<?php

declare(strict_types=1);

namespace App\Enums;

enum PlaceCorrectionStatus: string
{
    case Pending = 'pending';
    case InReview = 'in_review';
    case NeedsInformation = 'needs_information';
    case Accepted = 'accepted';
    case PartiallyAccepted = 'partially_accepted';
    case Rejected = 'rejected';
    case Withdrawn = 'withdrawn';
    case Superseded = 'superseded';

    public function isReviewDecision(): bool
    {
        return in_array($this, [
            self::InReview,
            self::NeedsInformation,
            self::Accepted,
            self::PartiallyAccepted,
            self::Rejected,
            self::Superseded,
        ], true);
    }

    public function appliesCanonicalMutation(): bool
    {
        return in_array($this, [self::Accepted, self::PartiallyAccepted], true);
    }
}
