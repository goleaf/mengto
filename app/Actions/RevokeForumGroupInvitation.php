<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\ForumGroupEventType;
use App\Enums\ForumGroupInvitationState;
use App\Models\ForumGroup;
use App\Models\ForumGroupInvitation;
use App\Models\User;
use App\Services\ForumGroupAudit;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final readonly class RevokeForumGroupInvitation
{
    public function __construct(
        private Gate $gate,
        private ForumGroupAudit $audit,
    ) {}

    public function handle(
        User $actor,
        ForumGroupInvitation $invitation,
    ): ForumGroupInvitation {
        $this->gate->forUser($actor)->authorize('invite', $invitation->group);

        return DB::transaction(function () use (
            $actor,
            $invitation,
        ): ForumGroupInvitation {
            $lockedInvitation = ForumGroupInvitation::query()
                ->with('invitee')
                ->lockForUpdate()
                ->findOrFail($invitation->id);
            $group = ForumGroup::query()
                ->lockForUpdate()
                ->findOrFail($lockedInvitation->forum_group_id);
            $this->gate->forUser($actor)->authorize('invite', $group);

            if ($lockedInvitation->state === ForumGroupInvitationState::Revoked) {
                return $lockedInvitation;
            }

            if ($lockedInvitation->state !== ForumGroupInvitationState::Pending) {
                throw ValidationException::withMessages([
                    'invitation' => __('forum_groups.validation.invitation_not_pending'),
                ]);
            }

            $lockedInvitation->forceFill([
                'state' => ForumGroupInvitationState::Revoked,
                'open_key' => null,
                'responded_at' => now(),
            ])->save();
            $this->audit->record(
                group: $group,
                actor: $actor,
                eventType: ForumGroupEventType::InvitationRevoked,
                reasonCode: 'invitation-revoked',
                summaryTranslationKey: 'forum_groups.events.invitation-revoked',
                subject: $lockedInvitation->invitee,
                metadata: ['invitation_id' => $lockedInvitation->id],
                idempotencyKey: "group:{$group->id}:invitation:{$lockedInvitation->id}:revoked",
            );

            return $lockedInvitation->refresh();
        }, 3);
    }
}
