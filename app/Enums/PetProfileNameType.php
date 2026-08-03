<?php

declare(strict_types=1);

namespace App\Enums;

enum PetProfileNameType: string
{
    case Nickname = 'nickname';
    case Previous = 'previous';
    case Shelter = 'shelter';
    case Official = 'official';
    case Localized = 'localized';
    case RespondsTo = 'responds-to';

    public function label(): string
    {
        return __("pet_profiles.name_types.{$this->value}");
    }
}
