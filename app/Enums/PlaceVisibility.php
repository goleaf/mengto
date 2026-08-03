<?php

declare(strict_types=1);

namespace App\Enums;

enum PlaceVisibility: string
{
    case Public = 'public';
    case Unlisted = 'unlisted';
    case Organization = 'organization';
    case Private = 'private';

    public function label(): string
    {
        return __('places.visibility.'.$this->value);
    }
}
