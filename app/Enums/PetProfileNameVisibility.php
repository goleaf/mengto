<?php

declare(strict_types=1);

namespace App\Enums;

enum PetProfileNameVisibility: string
{
    case Private = 'private';
    case Managers = 'managers';
    case Public = 'public';

    public function label(): string
    {
        return __("pet_profiles.name_visibility.{$this->value}");
    }
}
