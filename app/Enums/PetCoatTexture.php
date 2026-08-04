<?php

declare(strict_types=1);

namespace App\Enums;

enum PetCoatTexture: string
{
    case Smooth = 'smooth';
    case Straight = 'straight';
    case Wavy = 'wavy';
    case Curly = 'curly';
    case Wiry = 'wiry';
    case Silky = 'silky';
    case Plush = 'plush';
    case Coarse = 'coarse';
    case Corded = 'corded';
    case Other = 'other';

    public function label(): string
    {
        return __("pet_profiles.body_covering.options.coat_texture.{$this->value}");
    }
}
