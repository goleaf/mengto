<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\ForumEventInvitationStatus;
use App\Models\ForumEventInvitation;
use App\Models\User;
use App\Services\ForumEventAudit;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final readonly class RevokeForumEventInvitation
{
    public function __construct(
        private Gate $gate,
        private ForumEventAudit $audit,
    ) {}

    public function handle(User $actor, ForumEventInvitation $invitation): ForumEventInvitation
    {
        $event = $invitation->event;
        $this->gate->forUser($actor)->authorize('invite', $event);

        return DB::transaction(function () use ($actor, $event, $invitation): ForumEventInvitation {
            $lockedEvent = $event->newQuery()->lockForUpdate()->findOrFail($event->id);
            $locked = ForumEventInvitation::query()->lockForUpdate()->findOrFail($invitation->id);
            $this->gate->forUser($actor)->authorize('invite', $lockedEvent);

            if ($locked->forum_event_id !== $lockedEvent->id) {
                throw ValidationException::withMessages([
                    'invitation' => __('forum_events.validation.invitation_event_mismatch'),
                ]);
            }

            if ($locked->status === ForumEventInvitationStatus::Revoked) {
                return $locked;
            }

            if ($locked->status !== ForumEventInvitationStatus::Pending) {
                throw ValidationException::withMessages([
                    'invitation' => __('forum_events.validation.invitation_revoke_status'),
                ]);
            }

            $locked->forceFill([
                'status' => ForumEventInvitationStatus::Revoked,
                'responded_at' => now(),
            ])->save();
            $this->audit->record(
                event: $lockedEvent,
                actor: $actor,
                eventType: 'invitation-revoked',
                reasonCode: 'invitation-revoked',
                summaryTranslationKey: 'forum_events.history.invitation_revoked',
                subject: $locked->recipient,
                fromStatus: ForumEventInvitationStatus::Pending->value,
                toStatus: ForumEventInvitationStatus::Revoked->value,
                metadata: ['invitation_id' => $locked->id],
                idempotencyKey: 'event-invitation-revoked:'.$locked->id,
            );

            return $locked;
        }, 3);
    }
}
