<?php

namespace App\Enums;

enum ForumVisibility: string
{
    case Public = 'public';
    case Members = 'members';
    case Group = 'group';
    case Experts = 'experts';
    case Link = 'link';
    case Private = 'private';

    public function label(): string
    {
        return match ($this) {
            self::Public => 'Public',
            self::Members => 'Registered members',
            self::Group => 'Group members',
            self::Experts => 'Verified specialists',
            self::Link => 'Anyone with the link',
            self::Private => 'Private draft',
        };
    }
}
