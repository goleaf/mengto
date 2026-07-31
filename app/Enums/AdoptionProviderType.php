<?php

declare(strict_types=1);

namespace App\Enums;

enum AdoptionProviderType: string
{
    case PrivatePerson = 'private-person';
    case Organization = 'organization';

    public function label(): string
    {
        return __("adoption.provider_type.{$this->value}");
    }
}
