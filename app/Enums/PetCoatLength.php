<?php

declare(strict_types=1);

namespace App\Enums;

enum PetCoatLength: string
{
    case VeryShort = 'very-short';
    case Short = 'short';
    case Medium = 'medium';
    case Long = 'long';
    case Variable = 'variable';
    case Other = 'other';

    public function label(): string
    {
        return __("pet_profiles.body_covering.options.coat_length.{$this->value}");
    }
}
