<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\ForumEventRegistrationStatus;
use App\Enums\ForumEventInvitationStatus;
use App\Enums\ForumEventStatus;
use App\Enums\ForumEventUpdateAudience;
use App\Enums\ForumEventUpdateType;
use App\Enums\PlaceAccessGrantStatus;
use App\Enums\PlaceAccessPurpose;
use App\Models\ForumEvent;
use App\Models\ForumEventParticipationTransition;
use App\Models\ForumEventInvitation;
use App\Models\ForumEventRegistration;
use App\Models\ForumEventUpdate;
use App\Models\PlaceAccessGrant;
use App\Models\User;
use App\Services\ForumEventAudit;
use App\Services\ForumEventNotifier;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final readonly class CancelForumEvent
{
    public function __construct(
        private Gate $gate,
        private ForumEventAudit $audit,
        private ForumEventNotifier $notifier,
    ) {}

    public function handle(
        User $actor,
        ForumEvent $event,
        string $reasonCode,
        string $explanation,
        string $idempotencyKey,
    ): ForumEvent {
        $this->gate->forUser($actor)->authorize('cancel', $event);
        Validator::make([
            'reason_code' => $reasonCode,
            'explanation' => $explanation,
            'idempotency_key' => $idempotencyKey,
        ], [
            'reason_code' => ['required', 'string', 'max:100', 'regex:/^[a-z0-9-]+$/'],
            'explanation' => ['required', 'string', 'min:10', 'max:5000'],
            'idempotency_key' => ['required', 'string', 'min:16', 'max:190'],
        ])->validate();

        $cancelled = DB::transaction(function () use (
            $actor,
            $event,
            $explanation,
            $idempotencyKey,
            $reasonCode,
        ): ForumEvent {
            $locked = ForumEvent::query()
                ->lockForUpdate()
                ->findOrFail($event->id);
            $this->gate->forUser($actor)->authorize('cancel', $locked);

            if ($locked->status === ForumEventStatus::Cancelled) {
                return $locked;
            }

            if (! in_array($locked->status, [
                ForumEventStatus::Scheduled,
                ForumEventStatus::Published,
                ForumEventStatus::RegistrationScheduled,
                ForumEventStatus::RegistrationOpen,
                ForumEventStatus::RegistrationPaused,
                ForumEventStatus::RegistrationClosed,
                ForumEventStatus::Full,
                ForumEventStatus::WaitlistOnly,
                ForumEventStatus::Postponed,
                ForumEventStatus::Moved,
                ForumEventStatus::FormatChanged,
                ForumEventStatus::SafetySuspended,
                ForumEventStatus::Live,
            ], true)) {
                throw ValidationException::withMessages([
                    'cancellationForm' => __('forum_events.validation.cancellation_status'),
                ]);
            }

            $from = $locked->status;
            $locked->forceFill([
                'status' => ForumEventStatus::Cancelled,
                'cancelled_by_user_id' => $actor->id,
                'cancelled_at' => now(),
                'cancellation_reason_code' => $reasonCode,
                'lock_version' => $locked->lock_version + 1,
            ])->save();

            ForumEventRegistration::query()
                ->where('forum_event_id', $locked->id)
                ->whereIn('status', collect(ForumEventRegistrationStatus::cases())
                    ->filter(static fn (ForumEventRegistrationStatus $status): bool => $status->isActive())
                    ->map(static fn (ForumEventRegistrationStatus $status): string => $status->value)
                    ->all())
                ->orderBy('id')
                ->lockForUpdate()
                ->chunkById(100, function ($registrations) use ($actor): void {
                    foreach ($registrations as $registration) {
                        $from = $registration->status;
                        $registration->forceFill([
                            'status' => ForumEventRegistrationStatus::Cancelled,
                            'active_scope_key' => null,
                            'cancelled_at' => now(),
                            'cancellation_reason_code' => 'event-cancelled',
                            'lock_version' => $registration->lock_version + 1,
                            'status_changed_at' => now(),
                        ])->save();
                        ForumEventParticipationTransition::query()->firstOrCreate([
                            'forum_event_registration_id' => $registration->id,
                            'version' => $registration->lock_version,
                        ], [
                            'actor_user_id' => $actor->id,
                            'from_status' => $from->value,
                            'to_status' => ForumEventRegistrationStatus::Cancelled->value,
                            'reason_code' => 'event-cancelled',
                            'occurred_at' => now(),
                        ]);
                    }
                });
            PlaceAccessGrant::query()
                ->where('event_id', $locked->id)
                ->where('purpose', PlaceAccessPurpose::EventAttendance->value)
                ->where('status', PlaceAccessGrantStatus::Active->value)
                ->whereNull('revoked_at')
                ->update([
                    'status' => PlaceAccessGrantStatus::Revoked->value,
                    'revoked_by_user_id' => $actor->id,
                    'revoked_at' => now(),
                    'revocation_reason_code' => 'event-cancelled',
                    'updated_at' => now(),
                ]);
            ForumEventInvitation::query()
                ->where('forum_event_id', $locked->id)
                ->where('status', ForumEventInvitationStatus::Pending->value)
                ->lockForUpdate()
                ->update([
                    'active_pair_key' => null,
                    'status' => ForumEventInvitationStatus::Revoked->value,
                    'responded_at' => now(),
                    'updated_at' => now(),
                ]);
            $locked->occurrences()
                ->whereNotIn('status', [
                    ForumEventStatus::Completed->value,
                    ForumEventStatus::Cancelled->value,
                    ForumEventStatus::Archived->value,
                ])
                ->update([
                    'status' => ForumEventStatus::Cancelled->value,
                    'cancelled_at' => now(),
                    'cancellation_reason_code' => $reasonCode,
                    'updated_at' => now(),
                ]);

            ForumEventUpdate::query()->createOrFirst(
                ['idempotency_key' => 'event-cancellation-update:'.$idempotencyKey],
                [
                    'forum_event_id' => $locked->id,
                    'author_user_id' => $actor->id,
                    'stable_key' => 'event-update-'.Str::lower((string) Str::ulid()),
                    'type' => ForumEventUpdateType::Cancelled,
                    'audience' => ForumEventUpdateAudience::Public,
                    'title' => 'forum_events.updates.cancelled_title',
                    'body' => 'forum_events.updates.cancelled_body',
                    'published_at' => now(),
                ],
            );
            $this->audit->record(
                event: $locked,
                actor: $actor,
                eventType: 'cancelled',
                reasonCode: $reasonCode,
                summaryTranslationKey: 'forum_events.history.cancelled',
                fromStatus: $from->value,
                toStatus: ForumEventStatus::Cancelled->value,
                metadata: ['explanation' => trim($explanation)],
                idempotencyKey: 'event:cancel:'.$idempotencyKey,
            );

            return $locked;
        }, 3);

        ForumEventRegistration::query()
            ->where('forum_event_id', $cancelled->id)
            ->where('cancellation_reason_code', 'event-cancelled')
            ->with('user:id,actor_key,locale,status')
            ->orderBy('id')
            ->chunkById(100, function ($registrations) use ($cancelled): void {
                foreach ($registrations as $registration) {
                    $this->notifier->send(
                        $registration->user,
                        $cancelled,
                        'event-cancelled',
                        'forum_events.notifications.cancelled_title',
                        'forum_events.notifications.cancelled_body',
                        'event-cancelled:'.$cancelled->id.':'.$registration->user_id,
                    );
                }
            });

        return $cancelled;
    }
}
