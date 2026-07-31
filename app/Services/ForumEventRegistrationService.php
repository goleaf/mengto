<?php

declare(strict_types=1);

namespace App\Services;

use App\Data\RegisterForForumEventData;
use App\Enums\ForumEventFormat;
use App\Enums\ForumEventInvitationStatus;
use App\Enums\ForumEventPhotoConsent;
use App\Enums\ForumEventRegistrationPolicy;
use App\Enums\ForumEventRegistrationStatus;
use App\Models\ForumEvent;
use App\Models\ForumEventInvitation;
use App\Models\ForumEventRegistration;
use App\Models\PetProfile;
use App\Models\User;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

final readonly class ForumEventRegistrationService
{
    public function __construct(
        private Gate $gate,
        private ForumEventAudit $audit,
        private ForumEventNotifier $notifier,
    ) {}

    public function register(
        User $actor,
        ForumEvent $event,
        RegisterForForumEventData $data,
    ): ForumEventRegistration {
        $this->gate->forUser($actor)->authorize('register', $event);
        $this->validateRegistration($actor, $event, $data);

        if ($event->cost_minor > 0) {
            throw ValidationException::withMessages([
                'registrationForm' => __('forum_events.validation.checkout_unavailable'),
            ]);
        }

        $registration = DB::transaction(function () use ($actor, $data, $event): ForumEventRegistration {
            $lockedEvent = ForumEvent::query()
                ->lockForUpdate()
                ->findOrFail($event->id);
            $this->gate->forUser($actor)->authorize('register', $lockedEvent);

            if ($lockedEvent->registration_policy === ForumEventRegistrationPolicy::Invitation) {
                $hasInvitation = ForumEventInvitation::query()
                    ->where('forum_event_id', $lockedEvent->id)
                    ->where('invited_user_id', $actor->id)
                    ->where('status', ForumEventInvitationStatus::Accepted->value)
                    ->where('expires_at', '>', now())
                    ->exists();

                if (! $hasInvitation) {
                    throw ValidationException::withMessages([
                        'registrationForm' => __('forum_events.validation.invitation_required'),
                    ]);
                }
            }

            $existing = ForumEventRegistration::query()
                ->where('forum_event_id', $lockedEvent->id)
                ->where('user_id', $actor->id)
                ->lockForUpdate()
                ->first();

            if ($existing !== null && ! in_array($existing->status, [
                ForumEventRegistrationStatus::Cancelled,
                ForumEventRegistrationStatus::Declined,
            ], true)) {
                return $existing;
            }

            $requestedSeats = 1 + $data->guestCount;
            $remainingSeats = $this->remainingSeats($lockedEvent);
            $isFull = $remainingSeats !== null && $remainingSeats < $requestedSeats;
            $status = match (true) {
                $isFull && $lockedEvent->waitlist_enabled => ForumEventRegistrationStatus::Waitlisted,
                $isFull => throw ValidationException::withMessages([
                    'registrationForm' => __('forum_events.validation.full'),
                ]),
                $lockedEvent->registration_policy === ForumEventRegistrationPolicy::Approval => ForumEventRegistrationStatus::Pending,
                default => ForumEventRegistrationStatus::Confirmed,
            };
            $waitlistPosition = $status === ForumEventRegistrationStatus::Waitlisted
                ? $this->nextWaitlistPosition($lockedEvent)
                : null;
            $attributes = [
                'pet_profile_id' => $data->petProfileId,
                'idempotency_key' => $data->idempotencyKey,
                'status' => $status,
                'attendance_format' => $data->attendanceFormat,
                'guest_count' => $data->guestCount,
                'requirements_note' => $data->requirementsNote,
                'photo_consent' => $data->photoConsent,
                'requirements_accepted' => true,
                'waitlist_position' => $waitlistPosition,
                'check_in_method' => null,
                'checked_in_at' => null,
                'cancelled_at' => null,
                'cancellation_reason_code' => null,
                'lock_version' => $existing === null
                    ? 0
                    : $existing->lock_version + 1,
            ];

            if ($existing !== null) {
                $existing->forceFill($attributes)->save();
                $registration = $existing;
            } else {
                $registration = ForumEventRegistration::query()->create([
                    'forum_event_id' => $lockedEvent->id,
                    'user_id' => $actor->id,
                    'stable_key' => 'event-registration-'.Str::lower((string) Str::ulid()),
                    ...$attributes,
                ]);
            }

            $this->audit->record(
                event: $lockedEvent,
                actor: $actor,
                eventType: 'registration-created',
                reasonCode: 'registration-created',
                summaryTranslationKey: 'forum_events.history.registration_created',
                subject: $actor,
                toStatus: $status->value,
                metadata: [
                    'registration_id' => $registration->id,
                    'guest_count' => $registration->guest_count,
                    'waitlist_position' => $registration->waitlist_position,
                ],
                idempotencyKey: 'event:registration:'.$data->idempotencyKey,
            );

            return $registration;
        }, 3);

        $this->notifier->send(
            $actor,
            $event,
            'event-registration',
            'forum_events.notifications.registration_title',
            'forum_events.notifications.registration_body',
            'event-registration:'.$registration->id.':'.$registration->status->value,
            ['status' => $registration->status->label()],
        );

        return $registration;
    }

    public function cancel(
        User $actor,
        ForumEventRegistration $registration,
        string $reasonCode = 'participant-cancelled',
    ): ForumEventRegistration {
        $this->gate->forUser($actor)->authorize('cancelRegistration', $registration);

        [$cancelled, $promoted] = DB::transaction(function () use (
            $actor,
            $reasonCode,
            $registration,
        ): array {
            $lockedEvent = ForumEvent::query()
                ->lockForUpdate()
                ->findOrFail($registration->forum_event_id);
            $locked = ForumEventRegistration::query()
                ->lockForUpdate()
                ->findOrFail($registration->id);
            $this->gate->forUser($actor)->authorize('cancelRegistration', $locked);

            $from = $locked->status;
            $locked->forceFill([
                'status' => ForumEventRegistrationStatus::Cancelled,
                'waitlist_position' => null,
                'cancelled_at' => now(),
                'cancellation_reason_code' => $reasonCode,
                'lock_version' => $locked->lock_version + 1,
            ])->save();

            $this->audit->record(
                event: $lockedEvent,
                actor: $actor,
                eventType: 'registration-cancelled',
                reasonCode: $reasonCode,
                summaryTranslationKey: 'forum_events.history.registration_cancelled',
                subject: $actor,
                fromStatus: $from->value,
                toStatus: ForumEventRegistrationStatus::Cancelled->value,
                metadata: ['registration_id' => $locked->id],
            );

            return [$locked, $this->promoteFirstWaitlisted($lockedEvent, $actor)];
        }, 3);

        if ($promoted instanceof ForumEventRegistration) {
            $this->notifier->send(
                $promoted->user,
                $cancelled->event,
                'event-waitlist-promoted',
                'forum_events.notifications.promoted_title',
                'forum_events.notifications.promoted_body',
                'event-waitlist-promoted:'.$promoted->id.':'.$promoted->lock_version,
            );
        }

        return $cancelled;
    }

    public function review(
        User $actor,
        ForumEventRegistration $registration,
        bool $approve,
    ): ForumEventRegistration {
        $event = $registration->event;
        $this->gate->forUser($actor)->authorize('manageRegistrations', $event);

        $reviewed = DB::transaction(function () use (
            $actor,
            $approve,
            $event,
            $registration,
        ): ForumEventRegistration {
            $lockedEvent = ForumEvent::query()
                ->lockForUpdate()
                ->findOrFail($event->id);
            $locked = ForumEventRegistration::query()
                ->lockForUpdate()
                ->findOrFail($registration->id);
            $this->gate->forUser($actor)->authorize('manageRegistrations', $lockedEvent);

            if ($locked->status !== ForumEventRegistrationStatus::Pending) {
                throw ValidationException::withMessages([
                    'registration' => __('forum_events.validation.registration_not_pending'),
                ]);
            }

            $status = ForumEventRegistrationStatus::Declined;
            $waitlistPosition = null;

            if ($approve) {
                $remaining = $this->remainingSeats($lockedEvent);
                $requested = 1 + $locked->guest_count;

                if ($remaining !== null && $remaining < $requested) {
                    if (! $lockedEvent->waitlist_enabled) {
                        throw ValidationException::withMessages([
                            'registration' => __('forum_events.validation.full'),
                        ]);
                    }

                    $status = ForumEventRegistrationStatus::Waitlisted;
                    $waitlistPosition = $this->nextWaitlistPosition($lockedEvent);
                } else {
                    $status = ForumEventRegistrationStatus::Confirmed;
                }
            }

            $locked->forceFill([
                'status' => $status,
                'waitlist_position' => $waitlistPosition,
                'lock_version' => $locked->lock_version + 1,
            ])->save();

            $this->audit->record(
                event: $lockedEvent,
                actor: $actor,
                eventType: 'registration-reviewed',
                reasonCode: $approve ? 'registration-approved' : 'registration-declined',
                summaryTranslationKey: 'forum_events.history.registration_reviewed',
                subject: $locked->user,
                fromStatus: ForumEventRegistrationStatus::Pending->value,
                toStatus: $status->value,
                metadata: ['registration_id' => $locked->id],
            );

            return $locked;
        }, 3);

        $this->notifier->send(
            $reviewed->user,
            $event,
            'event-registration-reviewed',
            'forum_events.notifications.reviewed_title',
            'forum_events.notifications.reviewed_body',
            'event-registration-reviewed:'.$reviewed->id.':'.$reviewed->lock_version,
            ['status' => $reviewed->status->label()],
        );

        return $reviewed;
    }

    public function checkIn(
        User $actor,
        ForumEventRegistration $registration,
        string $method,
    ): ForumEventRegistration {
        $event = $registration->event;
        $this->gate->forUser($actor)->authorize('manageRegistrations', $event);
        Validator::make(
            ['method' => $method],
            ['method' => ['required', Rule::in(['manual', 'qr'])]],
        )->validate();

        return DB::transaction(function () use (
            $actor,
            $event,
            $method,
            $registration,
        ): ForumEventRegistration {
            $locked = ForumEventRegistration::query()
                ->lockForUpdate()
                ->findOrFail($registration->id);

            if ($locked->status === ForumEventRegistrationStatus::CheckedIn) {
                return $locked;
            }

            if ($locked->status !== ForumEventRegistrationStatus::Confirmed) {
                throw ValidationException::withMessages([
                    'registration' => __('forum_events.validation.check_in_status'),
                ]);
            }

            $locked->forceFill([
                'status' => ForumEventRegistrationStatus::CheckedIn,
                'check_in_method' => $method,
                'checked_in_at' => now(),
                'lock_version' => $locked->lock_version + 1,
            ])->save();
            $this->audit->record(
                event: $event,
                actor: $actor,
                eventType: 'attendee-checked-in',
                reasonCode: 'attendee-checked-in',
                summaryTranslationKey: 'forum_events.history.checked_in',
                subject: $locked->user,
                fromStatus: ForumEventRegistrationStatus::Confirmed->value,
                toStatus: ForumEventRegistrationStatus::CheckedIn->value,
                metadata: [
                    'registration_id' => $locked->id,
                    'method' => $method,
                ],
                idempotencyKey: 'event-check-in:'.$locked->id,
            );

            return $locked;
        }, 3);
    }

    public function remainingSeats(ForumEvent $event): ?int
    {
        if ($event->capacity === null) {
            return null;
        }

        $confirmed = $event->registrations()
            ->whereIn('status', [
                ForumEventRegistrationStatus::Confirmed->value,
                ForumEventRegistrationStatus::CheckedIn->value,
            ]);
        $used = (int) data_get($event->metadata, 'legacy_base_attendees', 0)
            + (clone $confirmed)->count()
            + (int) (clone $confirmed)->sum('guest_count');

        return max(0, $event->capacity - $used);
    }

    private function nextWaitlistPosition(ForumEvent $event): int
    {
        return ((int) $event->registrations()
            ->where('status', ForumEventRegistrationStatus::Waitlisted->value)
            ->max('waitlist_position')) + 1;
    }

    private function promoteFirstWaitlisted(
        ForumEvent $event,
        User $actor,
    ): ?ForumEventRegistration {
        $next = $event->registrations()
            ->where('status', ForumEventRegistrationStatus::Waitlisted->value)
            ->orderBy('waitlist_position')
            ->orderBy('id')
            ->lockForUpdate()
            ->first();

        if ($next === null) {
            return null;
        }

        $remaining = $this->remainingSeats($event);
        if ($remaining !== null && $remaining < 1 + $next->guest_count) {
            return null;
        }

        $status = $event->registration_policy === ForumEventRegistrationPolicy::Approval
            ? ForumEventRegistrationStatus::Pending
            : ForumEventRegistrationStatus::Confirmed;
        $next->forceFill([
            'status' => $status,
            'waitlist_position' => null,
            'lock_version' => $next->lock_version + 1,
        ])->save();
        $this->audit->record(
            event: $event,
            actor: $actor,
            eventType: 'waitlist-promoted',
            reasonCode: 'seat-released',
            summaryTranslationKey: 'forum_events.history.waitlist_promoted',
            subject: $next->user,
            fromStatus: ForumEventRegistrationStatus::Waitlisted->value,
            toStatus: $status->value,
            metadata: ['registration_id' => $next->id],
        );

        return $next;
    }

    private function validateRegistration(
        User $actor,
        ForumEvent $event,
        RegisterForForumEventData $data,
    ): void {
        Validator::make([
            'attendance_format' => $data->attendanceFormat->value,
            'guest_count' => $data->guestCount,
            'pet_profile_id' => $data->petProfileId,
            'requirements_note' => $data->requirementsNote,
            'photo_consent' => $data->photoConsent->value,
            'requirements_accepted' => $data->requirementsAccepted,
            'idempotency_key' => $data->idempotencyKey,
        ], [
            'attendance_format' => ['required', Rule::in($this->attendanceFormats($event))],
            'guest_count' => ['required', 'integer', 'min:0', 'max:10'],
            'pet_profile_id' => [
                'nullable',
                'integer',
                Rule::exists('pet_profiles', 'id')
                    ->where('user_id', $actor->id)
                    ->whereNull('deleted_at'),
            ],
            'requirements_note' => ['nullable', 'string', 'max:3000'],
            'photo_consent' => [
                'required',
                Rule::enum(ForumEventPhotoConsent::class),
            ],
            'requirements_accepted' => ['accepted'],
            'idempotency_key' => ['required', 'string', 'min:16', 'max:190'],
        ])->validate();

        if ($data->petProfileId !== null) {
            $pet = PetProfile::query()
                ->select(['id', 'user_id', 'birth_date'])
                ->findOrFail($data->petProfileId);

            if ($pet->user_id !== $actor->id) {
                throw ValidationException::withMessages([
                    'registrationForm.petProfileId' => __('forum_events.validation.pet_ownership'),
                ]);
            }

            $ageMonths = $pet->birth_date?->diffInMonths($event->starts_at);
            if ($ageMonths !== null
                && $event->minimum_animal_age_months !== null
                && $ageMonths < $event->minimum_animal_age_months
            ) {
                throw ValidationException::withMessages([
                    'registrationForm.petProfileId' => __('forum_events.validation.pet_too_young'),
                ]);
            }

            if ($ageMonths !== null
                && $event->maximum_animal_age_months !== null
                && $ageMonths > $event->maximum_animal_age_months
            ) {
                throw ValidationException::withMessages([
                    'registrationForm.petProfileId' => __('forum_events.validation.pet_too_old'),
                ]);
            }
        }
    }

    /** @return list<string> */
    private function attendanceFormats(ForumEvent $event): array
    {
        return match ($event->format) {
            ForumEventFormat::Physical => [ForumEventFormat::Physical->value],
            ForumEventFormat::Online => [ForumEventFormat::Online->value],
            ForumEventFormat::Hybrid => [
                ForumEventFormat::Physical->value,
                ForumEventFormat::Online->value,
            ],
        };
    }
}
