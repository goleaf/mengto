<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\ForumEventInvitationStatus;
use App\Models\ForumEvent;
use App\Models\ForumEventInvitation;
use App\Models\User;
use App\Services\ForumEventAudit;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final readonly class RespondToForumEventInvitation
{
    public function __construct(
        private Gate $gate,
        private ForumEventAudit $audit,
    ) {}

    public function handle(
        User $actor,
        ForumEventInvitation $invitation,
        bool $accept,
    ): ForumEventInvitation {
        if (! $actor->isActive() || $invitation->invited_user_id !== $actor->id) {
            throw new AuthorizationException;
        }

        [$responded, $expired] = DB::transaction(function () use ($accept, $actor, $invitation): array {
            $lockedEvent = ForumEvent::query()
                ->lockForUpdate()
                ->findOrFail($invitation->forum_event_id);
            $lockedActor = User::query()->lockForUpdate()->findOrFail($actor->id);
            $locked = ForumEventInvitation::query()
                ->lockForUpdate()
                ->findOrFail($invitation->id);

            if (! $lockedActor->isActive()
                || $locked->forum_event_id !== $lockedEvent->id
                || $locked->invited_user_id !== $lockedActor->id
            ) {
                throw new AuthorizationException;
            }

            if ($locked->expires_at->isPast()) {
                if ($locked->status === ForumEventInvitationStatus::Pending) {
                    $locked->forceFill([
                        'active_pair_key' => null,
                        'status' => ForumEventInvitationStatus::Expired,
                        'responded_at' => now(),
                    ])->save();
                }

                return [$locked, true];
            }

            if ($locked->status !== ForumEventInvitationStatus::Pending) {
                return [$locked, false];
            }

            $this->gate->forUser($lockedActor)->authorize('respondToInvitation', $lockedEvent);

            $status = $accept
                ? ForumEventInvitationStatus::Accepted
                : ForumEventInvitationStatus::Declined;
            $locked->forceFill([
                'active_pair_key' => $accept ? $locked->active_pair_key : null,
                'status' => $status,
                'responded_at' => now(),
            ])->save();
            $this->audit->record(
                event: $lockedEvent,
                actor: $lockedActor,
                eventType: 'invitation-responded',
                reasonCode: $accept ? 'invitation-accepted' : 'invitation-declined',
                summaryTranslationKey: 'forum_events.history.invitation_responded',
                subject: $lockedActor,
                fromStatus: ForumEventInvitationStatus::Pending->value,
                toStatus: $status->value,
                metadata: ['invitation_id' => $locked->id],
                idempotencyKey: 'event-invitation-response:'.$locked->id,
            );

            return [$locked, false];
        }, 3);

        if ($expired) {
            throw ValidationException::withMessages([
                'invitation' => __('forum_events.validation.invitation_expired'),
            ]);
        }

        return $responded;
    }
}
