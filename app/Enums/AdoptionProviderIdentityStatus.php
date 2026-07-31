<?php

declare(strict_types=1);

namespace App\Enums;

enum AdoptionProviderIdentityStatus: string
{
    case Unverified = 'unverified';
    case Pending = 'pending';
    case Verified = 'verified';
    case Expired = 'expired';
    case Suspended = 'suspended';
    case Rejected = 'rejected';
    case Revoked = 'revoked';

    public function label(): string
    {
        return __("adoption.identity_status.{$this->value}");
    }

    public function isVerified(): bool
    {
        return $this === self::Verified;
    }
}
