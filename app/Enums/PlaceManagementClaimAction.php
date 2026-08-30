<?php

declare(strict_types=1);

namespace App\Enums;

enum PlaceManagementClaimAction: string
{
    case Submitted = 'submitted';
    case EvidenceUploaded = 'evidence_uploaded';
    case ReviewStarted = 'review_started';
    case InformationRequested = 'information_requested';
    case InformationResubmitted = 'information_resubmitted';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Expired = 'expired';
    case Revoked = 'revoked';
    case Superseded = 'superseded';
    case ReviewerRecused = 'reviewer_recused';
    case AbuseReported = 'abuse_reported';
}
