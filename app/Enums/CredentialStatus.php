<?php

declare(strict_types=1);

namespace App\Enums;

enum CredentialStatus: string
{
    case Submitted = 'submitted';
    case InReview = 'in-review';
    case Verified = 'verified';
    case Expiring = 'expiring';
    case Expired = 'expired';
    case Rejected = 'rejected';
    case Suspended = 'suspended';
    case Revoked = 'revoked';

    public function label(): string
    {
        return __("credential_verification.status.{$this->value}");
    }
}
