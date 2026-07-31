<?php

declare(strict_types=1);

namespace App\Enums;

enum ContentPublicationStatus: string
{
    case Draft = 'draft';
    case Preparing = 'preparing';
    case Uploading = 'uploading';
    case Processing = 'processing';
    case PendingApproval = 'pending-approval';
    case Scheduled = 'scheduled';
    case Published = 'published';
    case Hidden = 'hidden';
    case Restricted = 'restricted';
    case Blocked = 'blocked';
    case Archived = 'archived';
    case DeletedByAuthor = 'deleted-by-author';
    case DeletedByModerator = 'deleted-by-moderator';
    case Restored = 'restored';
    case Expired = 'expired';
    case Moved = 'moved';

    public function label(): string
    {
        return __("content.publication_statuses.{$this->value}");
    }
}
