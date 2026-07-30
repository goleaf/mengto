<?php

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

    public function label(): string
    {
        return str($this->value)->headline()->toString();
    }
}
