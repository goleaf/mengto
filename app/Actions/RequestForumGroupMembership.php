<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\ForumGroupMembershipRequestData;
use App\Enums\ForumGroupEventType;
use App\Enums\ForumGroupMembershipState;
use App\Enums\ForumGroupRole;
use App\Enums\ForumGroupVisibility;
use App\Models\ForumGroup;
use App\Models\ForumGroupMembership;
use App\Models\User;
use App\Services\ForumGroupAudit;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

final readonly class RequestForumGroupMembership
{
    public function __construct(
        private Gate $gate,
        private ForumGroupAudit $audit,
    ) {}

    public function handle(
        User $user,
        ForumGroup $group,
        ForumGroupMembershipRequestData $data,
    ): ForumGroupMembership {
        Validator::make([
            'answers' => $data->answers,
            'idempotency_key' => $data->idempotencyKey,
        ], [
            'answers' => ['array', 'max:10'],
            'answers.*' => ['required', 'string', 'max:1000'],
            'idempotency_key' => ['required', 'string', 'min:16', 'max:190'],
        ])->validate();
        $this->validateAnswers($group, $data->answers);
        $existingMembership = $group->membershipFor($user);

        if ($existingMembership?->last_idempotency_key === $data->idempotencyKey
            && in_array($existingMembership->state, [
                ForumGroupMembershipState::Active,
                ForumGroupMembershipState::Pending,
            ], true)
        ) {
            return $existingMembership;
        }

        $this->gate->forUser($user)->authorize('requestMembership', $group);

        return DB::transaction(function () use ($data, $group, $user): ForumGroupMembership {
            $lockedGroup = ForumGroup::query()->lockForUpdate()->findOrFail($group->id);
            $this->gate->forUser($user)->authorize('requestMembership', $lockedGroup);
            $membership = ForumGroupMembership::query()
                ->where('forum_group_id', $lockedGroup->id)
                ->where('user_id', $user->id)
                ->lockForUpdate()
                ->first();

            if ($membership?->state === ForumGroupMembershipState::Banned) {
                throw new AuthorizationException;
            }

            if ($membership !== null
                && in_array($membership->state, [
                    ForumGroupMembershipState::Active,
                    ForumGroupMembershipState::Pending,
                ], true)
            ) {
                return $membership;
            }

            $state = $lockedGroup->visibility === ForumGroupVisibility::Public
                ? ForumGroupMembershipState::Active
                : ForumGroupMembershipState::Pending;
            $wasActive = $membership?->state === ForumGroupMembershipState::Active;
            $membership ??= new ForumGroupMembership([
                'forum_group_id' => $lockedGroup->id,
                'user_id' => $user->id,
                'role' => ForumGroupRole::Member,
            ]);
            $membership->forceFill([
                'state' => $state,
                'answers' => $data->answers,
                'requested_at' => now(),
                'reviewed_at' => $state === ForumGroupMembershipState::Active ? now() : null,
                'joined_at' => $state === ForumGroupMembershipState::Active ? now() : null,
                'ended_at' => null,
                'review_reason' => null,
                'restriction_reason' => null,
                'last_idempotency_key' => $data->idempotencyKey,
                'lock_version' => ($membership->exists ? $membership->lock_version : 0) + 1,
            ])->save();

            if ($state === ForumGroupMembershipState::Active && ! $wasActive) {
                $lockedGroup->increment('active_member_count');
            }

            $this->audit->record(
                group: $lockedGroup,
                actor: $user,
                eventType: $state === ForumGroupMembershipState::Active
                    ? ForumGroupEventType::MembershipApproved
                    : ForumGroupEventType::MembershipRequested,
                reasonCode: $state === ForumGroupMembershipState::Active
                    ? 'public-group-joined'
                    : 'membership-requested',
                summaryTranslationKey: $state === ForumGroupMembershipState::Active
                    ? 'forum_groups.events.membership-approved'
                    : 'forum_groups.events.membership-requested',
                subject: $user,
                metadata: ['visibility' => $lockedGroup->visibility->value],
                idempotencyKey: "group:{$lockedGroup->id}:membership:{$data->idempotencyKey}",
            );

            return $membership->refresh();
        }, 3);
    }

    /** @param array<string, string> $answers */
    private function validateAnswers(ForumGroup $group, array $answers): void
    {
        $questions = $group->membership_questions;

        if ($questions === []) {
            return;
        }

        $expected = array_map('strval', array_keys($questions));
        $provided = array_map('strval', array_keys($answers));
        sort($expected);
        sort($provided);

        if ($provided !== $expected) {
            throw ValidationException::withMessages([
                'answers' => __('forum_groups.validation.answers_required'),
            ]);
        }
    }
}
