<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\PlaceAccessPurpose;
use App\Models\ForumEvent;
use App\Models\Place;
use App\Models\PlaceAccessGrant;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

final readonly class GrantPlaceAccess
{
    public function __construct(private Gate $gate) {}

    public function handle(
        User $actor,
        Place $place,
        User $recipient,
        PlaceAccessPurpose $purpose,
        CarbonImmutable $validFrom,
        CarbonImmutable $validUntil,
        string $idempotencyKey,
        ?ForumEvent $event = null,
    ): PlaceAccessGrant {
        $this->gate->forUser($actor)->authorize('manageAccess', $place);

        Validator::make([
            'valid_from' => $validFrom->toAtomString(),
            'valid_until' => $validUntil->toAtomString(),
            'purpose' => $purpose->value,
            'idempotency_key' => $idempotencyKey,
        ], [
            'valid_from' => ['required', 'date'],
            'valid_until' => ['required', 'date', 'after:valid_from'],
            'purpose' => ['required', Rule::enum(PlaceAccessPurpose::class)],
            'idempotency_key' => ['required', 'string', 'min:16', 'max:190'],
        ])->validate();

        if ($validUntil->greaterThan($validFrom->addDays(30))) {
            throw ValidationException::withMessages([
                'valid_until' => __('places.validation.grant_window'),
            ]);
        }

        if ($event !== null && $event->place_id !== $place->id) {
            throw ValidationException::withMessages([
                'event' => __('places.validation.event_place_mismatch'),
            ]);
        }

        if ($purpose === PlaceAccessPurpose::EventAttendance
            && ($event === null || ! $event->canDiscloseAccessTo($recipient))
        ) {
            throw ValidationException::withMessages([
                'event' => __('places.validation.attendance_requires_confirmation'),
            ]);
        }

        if (! $recipient->isActive() || ! $recipient->hasVerifiedEmail()) {
            throw new AuthorizationException;
        }

        return DB::transaction(function () use (
            $actor,
            $place,
            $recipient,
            $purpose,
            $validFrom,
            $validUntil,
            $idempotencyKey,
            $event,
        ): PlaceAccessGrant {
            $lockedPlace = Place::query()->lockForUpdate()->findOrFail($place->id);
            $lockedRecipient = User::query()->lockForUpdate()->findOrFail($recipient->id);
            $lockedEvent = $event === null
                ? null
                : ForumEvent::query()->lockForUpdate()->findOrFail($event->id);
            $this->gate->forUser($actor)->authorize('manageAccess', $lockedPlace);

            if (! $lockedRecipient->isActive() || ! $lockedRecipient->hasVerifiedEmail()) {
                throw new AuthorizationException;
            }

            if ($lockedEvent !== null && $lockedEvent->place_id !== $lockedPlace->id) {
                throw ValidationException::withMessages([
                    'event' => __('places.validation.event_place_mismatch'),
                ]);
            }

            if ($purpose === PlaceAccessPurpose::EventAttendance
                && ($lockedEvent === null || ! $lockedEvent->canDiscloseAccessTo($lockedRecipient))
            ) {
                throw ValidationException::withMessages([
                    'event' => __('places.validation.attendance_requires_confirmation'),
                ]);
            }

            $existing = PlaceAccessGrant::query()
                ->where('idempotency_key', $idempotencyKey)
                ->lockForUpdate()
                ->first();

            if ($existing !== null) {
                if ($existing->place_id !== $lockedPlace->id
                    || $existing->user_id !== $lockedRecipient->id
                    || $existing->event_id !== $lockedEvent?->id
                    || $existing->purpose !== $purpose
                ) {
                    throw ValidationException::withMessages([
                        'idempotency_key' => __('places.validation.idempotency_conflict'),
                    ]);
                }

                return $existing;
            }

            return PlaceAccessGrant::query()->create([
                'place_id' => $lockedPlace->id,
                'user_id' => $lockedRecipient->id,
                'event_id' => $lockedEvent?->id,
                'issued_by_user_id' => $actor->id,
                'purpose' => $purpose,
                'may_view_exact_location' => true,
                'valid_from' => $validFrom,
                'valid_until' => $validUntil,
                'idempotency_key' => $idempotencyKey,
            ]);
        }, 3);
    }
}
