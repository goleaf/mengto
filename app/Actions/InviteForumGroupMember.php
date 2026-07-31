<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\ForumGroupInvitationData;
use App\Enums\ForumGroupEventType;
use App\Enums\ForumGroupInvitationState;
use App\Enums\ForumGroupMembershipState;
use App\Enums\ForumGroupRole;
use App\Enums\ForumGroupStatus;
use App\Models\ForumGroup;
use App\Models\ForumGroupInvitation;
use App\Models\User;
use App\Services\ForumGroupAudit;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

final readonly class InviteForumGroupMember
{
    public function __construct(
        private Gate $gate,
        private ForumGroupAudit $audit,
    ) {}

    public function handle(
        User $inviter,
        ForumGroup $group,
        User $invitee,
        ForumGroupInvitationData $data,
    ): ForumGroupInvitation {
        Validator::make([
            'role' => $data->role->value,
            'message' => $data->message,
            'expires_at' => $data->expiresAt,
            'idempotency_key' => $data->idempotencyKey,
        ], [
            'role' => [
                'required',
                Rule::in([
                    ForumGroupRole::Member->value,
                    ForumGroupRole::RestrictedMember->value,
                ]),
            ],
            'message' => ['nullable', 'string', 'max:1000'],
            'expires_at' => ['required', 'date', 'after:+59 minutes', 'before_or_equal:+30 days'],
            'idempotency_key' => ['required', 'string', 'min:16', 'max:190'],
        ])->validate();
        $this->gate->forUser($inviter)->authorize('invite', $group);

        if (! $invitee->isActive()
            || ! $invitee->hasVerifiedEmail()
            || $invitee->id === $inviter->id
        ) {
            throw new AuthorizationException;
        }

        if (ForumGroupInvitation::query()
            ->where('invited_by_user_id', $inviter->id)
            ->where('created_at', '>=', now()->subDay())
            ->count() >= 20
        ) {
            throw ValidationException::withMessages([
                'invitation' => __('forum_groups.validation.invitation_rate_limited'),
            ]);
        }

        return DB::transaction(function () use (
            $data,
            $group,
            $invitee,
            $inviter,
        ): ForumGroupInvitation {
            $lockedGroup = ForumGroup::query()->lockForUpdate()->findOrFail($group->id);
            $this->gate->forUser($inviter)->authorize('invite', $lockedGroup);

            if ($lockedGroup->status !== ForumGroupStatus::Active) {
                throw ValidationException::withMessages([
                    'group' => __('forum_groups.validation.group_not_active'),
                ]);
            }

            if ($lockedGroup->memberships()
                ->where('user_id', $invitee->id)
                ->where('state', ForumGroupMembershipState::Active->value)
                ->exists()
            ) {
                throw ValidationException::withMessages([
                    'invitee' => __('forum_groups.validation.already_member'),
                ]);
            }

            $existing = ForumGroupInvitation::query()
                ->where('idempotency_key', $data->idempotencyKey)
                ->lockForUpdate()
                ->first();

            if ($existing !== null) {
                if ($existing->forum_group_id !== $lockedGroup->id
                    || $existing->invited_user_id !== $invitee->id
                ) {
                    throw new AuthorizationException;
                }

                return $existing;
            }

            $openKey = "{$lockedGroup->id}:{$invitee->id}";
            $openInvitation = ForumGroupInvitation::query()
                ->where('open_key', $openKey)
                ->lockForUpdate()
                ->first();

            if ($openInvitation !== null && $openInvitation->hasExpired()) {
                $openInvitation->forceFill([
                    'state' => ForumGroupInvitationState::Expired,
                    'open_key' => null,
                    'responded_at' => now(),
                ])->save();
                $openInvitation = null;
            }

            if ($openInvitation !== null) {
                throw ValidationException::withMessages([
                    'invitee' => __('forum_groups.validation.invitation_pending'),
                ]);
            }

            $invitation = ForumGroupInvitation::query()->create([
                'forum_group_id' => $lockedGroup->id,
                'invited_user_id' => $invitee->id,
                'invited_by_user_id' => $inviter->id,
                'role' => $data->role,
                'state' => ForumGroupInvitationState::Pending,
                'message' => $data->message,
                'open_key' => $openKey,
                'idempotency_key' => $data->idempotencyKey,
                'expires_at' => $data->expiresAt,
            ]);

            $this->audit->record(
                group: $lockedGroup,
                actor: $inviter,
                eventType: ForumGroupEventType::InvitationCreated,
                reasonCode: 'member-invited',
                summaryTranslationKey: 'forum_groups.events.invitation-created',
                subject: $invitee,
                metadata: [
                    'invitation_id' => $invitation->id,
                    'role' => $data->role->value,
                    'expires_at' => $data->expiresAt->toAtomString(),
                ],
                idempotencyKey: "group:{$lockedGroup->id}:invite:{$data->idempotencyKey}",
            );

            return $invitation->load(['group', 'invitee:id,name', 'inviter:id,name']);
        }, 3);
    }
}
