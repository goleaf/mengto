<?php

declare(strict_types=1);

namespace App\Enums;

enum PetIdentifyingMarkVisibility: string
{
    case Public = 'public';
    case Verification = 'verification';

    public function label(): string
    {
        return __("pet_profiles.identifying_marks.visibility.{$this->value}");
    }
}
