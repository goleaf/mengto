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
        $event = $invitation->event;
        $this->gate->forUser($actor)->authorize('respondToInvitation', $event);

        return DB::transaction(function () use ($accept, $actor, $event, $invitation): ForumEventInvitation {
            $locked = ForumEventInvitation::query()
                ->lockForUpdate()
                ->findOrFail($invitation->id);

            if ($locked->invited_user_id !== $actor->id) {
                abort(403);
            }

            if ($locked->expires_at->isPast()) {
                $locked->forceFill([
                    'status' => ForumEventInvitationStatus::Expired,
                    'responded_at' => now(),
                ])->save();

                throw ValidationException::withMessages([
                    'invitation' => __('forum_events.validation.invitation_expired'),
                ]);
            }

            if ($locked->status !== ForumEventInvitationStatus::Pending) {
                return $locked;
            }

            $status = $accept
                ? ForumEventInvitationStatus::Accepted
                : ForumEventInvitationStatus::Declined;
            $locked->forceFill([
                'status' => $status,
                'responded_at' => now(),
            ])->save();
            $this->audit->record(
                event: $event,
                actor: $actor,
                eventType: 'invitation-responded',
                reasonCode: $accept ? 'invitation-accepted' : 'invitation-declined',
                summaryTranslationKey: 'forum_events.history.invitation_responded',
                subject: $actor,
                fromStatus: ForumEventInvitationStatus::Pending->value,
                toStatus: $status->value,
                metadata: ['invitation_id' => $locked->id],
                idempotencyKey: 'event-invitation-response:'.$locked->id,
            );

            return $locked;
        }, 3);
    }
}
