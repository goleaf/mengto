<?php

declare(strict_types=1);

namespace App\Enums;

enum ForumGroupRole: string
{
    case Owner = 'owner';
    case Administrator = 'administrator';
    case Moderator = 'moderator';
    case Steward = 'steward';
    case Member = 'member';
    case RestrictedMember = 'restricted-member';

    public function label(): string
    {
        return __("forum_groups.roles.{$this->value}");
    }

    public function canManageMembership(): bool
    {
        return in_array($this, [
            self::Owner,
            self::Administrator,
            self::Moderator,
        ], true);
    }

    public function canInvite(): bool
    {
        return in_array($this, [
            self::Owner,
            self::Administrator,
            self::Steward,
        ], true);
    }
}
