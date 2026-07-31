<?php

declare(strict_types=1);

namespace App\Enums;

enum ForumGroupVisibility: string
{
    case Public = 'public';
    case RequestToJoin = 'request-to-join';
    case Private = 'private';
    case Unlisted = 'unlisted';

    public function label(): string
    {
        return __("forum_groups.visibility.{$this->value}");
    }

    public function isDiscoverable(): bool
    {
        return in_array($this, [self::Public, self::RequestToJoin], true);
    }
}
