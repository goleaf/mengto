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
use App\Services\SocialBlockService;
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
        private SocialBlockService $blocks,
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
        if ($this->blocks->accountBlockedBetween([$actor->id], [$recipient->id])) {
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
            $lockedEvent = ForumEvent::query()->lockForUpdate()->findOrFail($event->id);
            $lockedActor = User::query()->lockForUpdate()->findOrFail($actor->id);
            $lockedRecipient = User::query()->lockForUpdate()->findOrFail($recipient->id);
            $this->gate->forUser($lockedActor)->authorize('invite', $lockedEvent);

            if (! $lockedRecipient->isActive()
                || $lockedRecipient->id === $lockedActor->id
                || $this->blocks->accountBlockedBetween([$lockedActor->id], [$lockedRecipient->id])
            ) {
                throw ValidationException::withMessages([
                    'invitationForm.recipient' => __('forum_events.validation.invitation_recipient'),
                ]);
            }

            if ($lockedEvent->responsible_organization_id !== null
                && ! $lockedEvent->responsibleOrganization
                    ->memberships()
                    ->where('user_id', $lockedRecipient->id)
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

            $requestChecksum = $this->requestChecksum(
                $lockedActor,
                $lockedEvent,
                $lockedRecipient,
                $expiresAt,
                $idempotencyKey,
            );
            $existingByIdempotency = ForumEventInvitation::query()
                ->where('idempotency_key', $idempotencyKey)
                ->lockForUpdate()
                ->first();

            if ($existingByIdempotency !== null) {
                if (! hash_equals(
                    $existingByIdempotency->request_checksum
                        ?? $this->storedRequestChecksum($existingByIdempotency),
                    $requestChecksum,
                )) {
                    throw ValidationException::withMessages([
                        'invitationForm.recipient' => __('forum_events.validation.idempotency_conflict'),
                    ]);
                }

                return $existingByIdempotency;
            }

            $activePairKey = hash('sha256', $lockedEvent->id.'|'.$lockedRecipient->id);
            $existing = ForumEventInvitation::query()
                ->where('active_pair_key', $activePairKey)
                ->lockForUpdate()
                ->first();

            if ($existing !== null && $existing->expires_at->isPast()) {
                $existing->forceFill([
                    'active_pair_key' => null,
                    'status' => ForumEventInvitationStatus::Expired,
                    'responded_at' => $existing->responded_at ?? now(),
                ])->save();
                $existing = null;
            }

            if ($existing !== null) {
                return $existing;
            }

            $invitation = ForumEventInvitation::query()->create([
                'forum_event_id' => $lockedEvent->id,
                'invited_by_user_id' => $lockedActor->id,
                'invited_user_id' => $lockedRecipient->id,
                'stable_key' => 'event-invitation-'.Str::lower((string) Str::ulid()),
                'idempotency_key' => $idempotencyKey,
                'active_pair_key' => $activePairKey,
                'request_checksum' => $requestChecksum,
                'status' => ForumEventInvitationStatus::Pending,
                'expires_at' => $expiresAt,
            ]);

            $this->audit->record(
                event: $event,
                actor: $lockedActor,
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

    private function requestChecksum(
        User $actor,
        ForumEvent $event,
        User $recipient,
        CarbonImmutable $expiresAt,
        string $idempotencyKey,
    ): string {
        return hash('sha256', json_encode([
            'event_id' => $event->id,
            'inviter_id' => $actor->id,
            'recipient_id' => $recipient->id,
            'expires_at' => $expiresAt->toISOString(),
            'idempotency_key' => $idempotencyKey,
        ], JSON_THROW_ON_ERROR));
    }

    private function storedRequestChecksum(ForumEventInvitation $invitation): string
    {
        return hash('sha256', json_encode([
            'event_id' => $invitation->forum_event_id,
            'inviter_id' => $invitation->invited_by_user_id,
            'recipient_id' => $invitation->invited_user_id,
            'expires_at' => $invitation->expires_at->toISOString(),
            'idempotency_key' => $invitation->idempotency_key,
        ], JSON_THROW_ON_ERROR));
    }
}
