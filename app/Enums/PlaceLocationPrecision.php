<?php

declare(strict_types=1);

namespace App\Enums;

enum PlaceLocationPrecision: string
{
    case PublicRegion = 'public_region';
    case PublicPoint = 'public_point';
    case PrivateExact = 'private_exact';

    public function label(): string
    {
        return __('places.submissions.location_precision.'.$this->value);
    }
}
