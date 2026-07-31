<?php

declare(strict_types=1);

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
        return __("forum.visibility.{$this->value}");
    }
}
