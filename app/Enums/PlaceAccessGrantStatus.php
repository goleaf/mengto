<?php

declare(strict_types=1);

namespace App\Enums;

enum PlaceAccessGrantStatus: string
{
    case Active = 'active';
    case Revoked = 'revoked';
    case Expired = 'expired';

    public function label(): string
    {
        return __('places.access_grant_statuses.'.$this->value);
    }
}
