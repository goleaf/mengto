<?php

namespace App\Enums;

enum ForumSubscriptionLevel: string
{
    case All = 'all';
    case Experts = 'experts';
    case AuthorUpdates = 'author-updates';
    case Mentions = 'mentions';
    case Accepted = 'accepted';
    case Digest = 'digest';
    case None = 'none';

    public function label(): string
    {
        return match ($this) {
            self::All => 'All replies',
            self::Experts => 'Expert replies',
            self::AuthorUpdates => 'Author updates',
            self::Mentions => 'Mentions only',
            self::Accepted => 'Accepted answer',
            self::Digest => 'Daily digest',
            self::None => 'No notifications',
        };
    }
}
