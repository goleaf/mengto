<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\ForumEventInvitationStatus;
use App\Enums\OrganizationMembershipStatus;
use App\Models\ForumEvent;
use App\Models\ForumEventInvitation;
use App\Models\User;
use App\Services\ForumEventAudit;
use App\Services\ForumEventNotifier;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final readonly class InviteToForumEvent
{
    public function __construct(
        private Gate $gate,
        private ForumEventAudit $audit,
        private ForumEventNotifier $notifier,
    ) {}

    public function handle(
        User $actor,
        ForumEvent $event,
        User $recipient,
        CarbonImmutable $expiresAt,
        string $idempotencyKey,
    ): ForumEventInvitation {
        $this->gate->forUser($actor)->authorize('invite', $event);
        Validator::make([
            'recipient_id' => $recipient->id,
            'expires_at' => $expiresAt->toAtomString(),
            'idempotency_key' => $idempotencyKey,
        ], [
            'recipient_id' => ['required', 'integer', 'different:actor_id'],
            'expires_at' => ['required', 'date', 'after:now', 'before_or_equal:'.now()->addMonths(3)->toAtomString()],
            'idempotency_key' => ['required', 'string', 'min:16', 'max:190'],
        ])->validate();

        if (! $recipient->isActive() || $recipient->id === $actor->id) {
            throw ValidationException::withMessages([
                'invitationForm.recipient' => __('forum_events.validation.invitation_recipient'),
            ]);
        }
        if ($event->responsible_organization_id !== null
            && ! $event->responsibleOrganization
                ->memberships()
                ->where('user_id', $recipient->id)
                ->where('status', OrganizationMembershipStatus::Active->value)
                ->where(function ($expiry): void {
                    $expiry->whereNull('expires_at')->orWhere('expires_at', '>', now());
                })
                ->exists()
        ) {
            throw ValidationException::withMessages([
                'invitationForm.recipient' => __('forum_events.validation.organization_membership_required'),
            ]);
        }

        $invitation = DB::transaction(function () use (
            $actor,
            $event,
            $expiresAt,
            $idempotencyKey,
            $recipient,
        ): ForumEventInvitation {
            ForumEvent::query()->lockForUpdate()->findOrFail($event->id);
            $existing = ForumEventInvitation::query()
                ->where('forum_event_id', $event->id)
                ->where('invited_user_id', $recipient->id)
                ->lockForUpdate()
                ->first();

            if ($existing?->isCurrent() === true
                || $existing?->status === ForumEventInvitationStatus::Accepted
            ) {
                return $existing;
            }

            if ($existing !== null) {
                $existing->forceFill([
                    'invited_by_user_id' => $actor->id,
                    'idempotency_key' => $idempotencyKey,
                    'status' => ForumEventInvitationStatus::Pending,
                    'expires_at' => $expiresAt,
                    'responded_at' => null,
                ])->save();
                $invitation = $existing;
            } else {
                $invitation = ForumEventInvitation::query()->create([
                    'forum_event_id' => $event->id,
                    'invited_by_user_id' => $actor->id,
                    'invited_user_id' => $recipient->id,
                    'stable_key' => 'event-invitation-'.Str::lower((string) Str::ulid()),
                    'idempotency_key' => $idempotencyKey,
                    'status' => ForumEventInvitationStatus::Pending,
                    'expires_at' => $expiresAt,
                ]);
            }

            $this->audit->record(
                event: $event,
                actor: $actor,
                eventType: 'invited',
                reasonCode: 'event-invitation-created',
                summaryTranslationKey: 'forum_events.history.invited',
                subject: $recipient,
                toStatus: ForumEventInvitationStatus::Pending->value,
                metadata: ['invitation_id' => $invitation->id],
                idempotencyKey: 'event:invitation:'.$idempotencyKey,
            );

            return $invitation;
        }, 3);

        $this->notifier->send(
            $recipient,
            $event,
            'event-invitation',
            'forum_events.notifications.invitation_title',
            'forum_events.notifications.invitation_body',
            'event-invitation:'.$invitation->id.':'.$invitation->updated_at?->timestamp,
            ['organizer' => $actor->name],
        );

        return $invitation;
    }
}
