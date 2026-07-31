<?php

declare(strict_types=1);

namespace App\Enums;

enum ForumGroupEventType: string
{
    case Created = 'created';
    case Updated = 'updated';
    case MembershipRequested = 'membership-requested';
    case MembershipApproved = 'membership-approved';
    case MembershipRejected = 'membership-rejected';
    case MemberLeft = 'member-left';
    case MemberRemoved = 'member-removed';
    case MemberBanned = 'member-banned';
    case InvitationCreated = 'invitation-created';
    case InvitationAccepted = 'invitation-accepted';
    case InvitationDeclined = 'invitation-declined';
    case InvitationRevoked = 'invitation-revoked';
    case InvitationExpired = 'invitation-expired';
    case RoleChanged = 'role-changed';
    case OwnershipTransferred = 'ownership-transferred';
    case Closed = 'closed';
    case Reopened = 'reopened';
    case Archived = 'archived';
    case Reported = 'reported';

    public function label(): string
    {
        return __("forum_groups.events.{$this->value}");
    }
}
