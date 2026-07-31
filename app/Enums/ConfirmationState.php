<?php

declare(strict_types=1);

namespace App\Enums;

enum ConfirmationState: string
{
    case NotRequested = 'not-requested';
    case AwaitingConfirmation = 'awaiting-confirmation';
    case GatheringEvidence = 'gathering-evidence';
    case CommunitySupported = 'community-supported';
    case CommunityConfirmed = 'community-confirmed';
    case Disputed = 'disputed';
    case InsufficientEvidence = 'insufficient-evidence';
    case ModeratorReviewed = 'moderator-reviewed';
    case ExpertReviewed = 'expert-reviewed';
    case Outdated = 'outdated';
    case Withdrawn = 'withdrawn';
    case Rejected = 'rejected';
}
