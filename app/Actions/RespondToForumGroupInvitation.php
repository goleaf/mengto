<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\ForumGroupEventType;
use App\Enums\ForumGroupInvitationState;
use App\Enums\ForumGroupMembershipState;
use App\Enums\ForumGroupStatus;
use App\Models\ForumGroup;
use App\Models\ForumGroupInvitation;
use App\Models\ForumGroupMembership;
use App\Models\SocialActor;
use App\Models\User;
use App\Services\CommunityMembershipActorEligibility;
use App\Services\ForumGroupAudit;
use App\Services\SocialActorResolver;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final readonly class RespondToForumGroupInvitation
{
    public function __construct(
        private ForumGroupAudit $audit,
        private SocialActorResolver $actors,
        private CommunityMembershipActorEligibility $actorEligibility,
    ) {}

    public function handle(
        User $invitee,
        ForumGroupInvitation $invitation,
        bool $accept,
        ?SocialActor $socialActor = null,
    ): ForumGroupInvitation {
        if (! $invitee->isActive() || $invitation->invited_user_id !== $invitee->id) {
            throw new AuthorizationException;
        }

        $membershipActor = null;

        if ($accept) {
            $group = ForumGroup::query()->findOrFail($invitation->forum_group_id);
            $membershipActor = $socialActor ?? $this->actors->forUser($invitee);

            if (! $this->actorEligibility->allows($invitee, $group, $membershipActor)) {
                throw new AuthorizationException;
            }
        }

        $result = DB::transaction(function () use (
            $accept,
            $invitation,
            $invitee,
            $membershipActor,
        ): ForumGroupInvitation {
            $lockedInvitation = ForumGroupInvitation::query()
                ->lockForUpdate()
                ->findOrFail($invitation->id);
            $group = ForumGroup::query()
                ->lockForUpdate()
                ->findOrFail($lockedInvitation->forum_group_id);

            if ($lockedInvitation->invited_user_id !== $invitee->id) {
                throw new AuthorizationException;
            }

            $targetState = $accept
                ? ForumGroupInvitationState::Accepted
                : ForumGroupInvitationState::Declined;

            if ($lockedInvitation->state === $targetState) {
                return $lockedInvitation;
            }

            if ($lockedInvitation->state !== ForumGroupInvitationState::Pending) {
                throw ValidationException::withMessages([
                    'invitation' => __('forum_groups.validation.invitation_not_pending'),
                ]);
            }

            if ($accept && $group->status !== ForumGroupStatus::Active) {
                throw ValidationException::withMessages([
                    'group' => __('forum_groups.validation.group_not_active'),
                ]);
            }

            if ($lockedInvitation->hasExpired()) {
                $lockedInvitation->forceFill([
                    'state' => ForumGroupInvitationState::Expired,
                    'open_key' => null,
                    'responded_at' => now(),
                ])->save();
                $this->audit->record(
                    group: $group,
                    actor: $invitee,
                    eventType: ForumGroupEventType::InvitationExpired,
                    reasonCode: 'invitation-expired',
                    summaryTranslationKey: 'forum_groups.events.invitation-expired',
                    subject: $invitee,
                    metadata: ['invitation_id' => $lockedInvitation->id],
                    idempotencyKey: "group:{$group->id}:invitation:{$lockedInvitation->id}:expired",
                );

                return $lockedInvitation->refresh();
            }

            if ($accept) {
                if (! $membershipActor instanceof SocialActor) {
                    throw new AuthorizationException;
                }

                if (! $this->actorEligibility->allows($invitee, $group, $membershipActor)) {
                    throw new AuthorizationException;
                }

                $membership = ForumGroupMembership::query()
                    ->where('forum_group_id', $group->id)
                    ->where('social_actor_id', $membershipActor->id)
                    ->lockForUpdate()
                    ->first();

                if ($membership?->state === ForumGroupMembershipState::Banned) {
                    throw new AuthorizationException;
                }

                $wasActive = $membership?->state === ForumGroupMembershipState::Active;
                $membership ??= new ForumGroupMembership([
                    'forum_group_id' => $group->id,
                    'user_id' => $invitee->id,
                    'social_actor_id' => $membershipActor->id,
                ]);
                $membership->forceFill([
                    'role' => $lockedInvitation->role,
                    'state' => ForumGroupMembershipState::Active,
                    'notification_level' => 'important',
                    'accepted_rules_version' => $group->rules_version,
                    'accepted_rules_at' => now(),
                    'reviewed_by_user_id' => $lockedInvitation->invited_by_user_id,
                    'review_reason' => 'invitation-accepted',
                    'reviewed_at' => now(),
                    'joined_at' => now(),
                    'ended_at' => null,
                    'restriction_reason' => null,
                    'last_idempotency_key' => "invitation:{$lockedInvitation->id}:accepted",
                    'lock_version' => ($membership->exists ? $membership->lock_version : 0) + 1,
                ])->save();

                if (! $wasActive) {
                    $group->increment('active_member_count');
                }
            }

            $lockedInvitation->forceFill([
                'state' => $targetState,
                'open_key' => null,
                'responded_at' => now(),
            ])->save();
            $eventType = $accept
                ? ForumGroupEventType::InvitationAccepted
                : ForumGroupEventType::InvitationDeclined;

            $this->audit->record(
                group: $group,
                actor: $invitee,
                eventType: $eventType,
                reasonCode: $eventType->value,
                summaryTranslationKey: "forum_groups.events.{$eventType->value}",
                subject: $invitee,
                metadata: array_filter([
                    'invitation_id' => $lockedInvitation->id,
                    'social_actor_key' => $membershipActor?->actor_key,
                    'social_actor_type' => $membershipActor?->actor_type->value,
                    'rules_version' => $accept ? $group->rules_version : null,
                ], static fn (mixed $value): bool => $value !== null),
                idempotencyKey: "group:{$group->id}:invitation:{$lockedInvitation->id}:{$eventType->value}",
            );

            return $lockedInvitation->refresh();
        }, 3);

        if ($result->state === ForumGroupInvitationState::Expired) {
            throw ValidationException::withMessages([
                'invitation' => __('forum_groups.validation.invitation_expired'),
            ]);
        }

        return $result;
    }
}
