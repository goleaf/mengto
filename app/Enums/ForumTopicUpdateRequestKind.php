<?php

declare(strict_types=1);

namespace App\Enums;

enum ForumTopicUpdateRequestKind: string
{
    case UpdateRequest = 'update-request';
    case CommunityProposal = 'community-proposal';

    public function label(): string
    {
        return __("forum_topic_lifecycle.request_kinds.{$this->value}");
    }
}
