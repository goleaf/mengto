<?php

declare(strict_types=1);

namespace App\Enums;

enum ForumEventVerificationStatus: string
{
    case Confirmed = 'confirmed';
    case ReportedByParticipant = 'reported_by_participant';
    case VerifiedByOrganization = 'verified_by_organization';
    case VerifiedByProfessional = 'verified_by_professional';
    case Unknown = 'unknown';
    case NotAssessed = 'not_assessed';
    case Expired = 'expired';
    case NotApplicable = 'not_applicable';
    case Disputed = 'disputed';
    case RequiresManualReview = 'requires_manual_review';

    public function label(): string
    {
        return __('forum_events.verification_statuses.'.$this->value);
    }
}
