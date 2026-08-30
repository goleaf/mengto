<?php

declare(strict_types=1);

namespace App\Enums;

enum PlacePublicLocationPrecision: string
{
    case Region = 'region';
    case ApproximatePoint = 'approximate_point';

    public function label(): string
    {
        return __('places.public_location_precision.'.$this->value);
    }
}
