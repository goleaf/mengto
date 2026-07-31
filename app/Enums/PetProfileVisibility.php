<?php

declare(strict_types=1);

namespace App\Enums;

enum PetProfileVisibility: string
{
    case Public = 'public';
    case Authenticated = 'authenticated';
    case Followers = 'followers';
    case Friends = 'friends';
    case Family = 'family';
    case Link = 'link';
    case Private = 'private';
    case Hidden = 'hidden';

    public function label(): string
    {
        return __("pet_profiles.visibility.{$this->value}");
    }

    public static function fromStored(?string $value): self
    {
        return match ($value) {
            'everyone' => self::Public,
            'members' => self::Authenticated,
            'circle' => self::Friends,
            'owners' => self::Private,
            default => self::tryFrom((string) $value) ?? self::Private,
        };
    }
}
