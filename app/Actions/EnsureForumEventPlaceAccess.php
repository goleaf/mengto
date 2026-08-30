<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\PlaceAccessPurpose;
use App\Enums\PlaceStatus;
use App\Models\ForumEvent;
use App\Models\Place;
use App\Models\PlaceAccessGrant;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Support\Facades\DB;

final readonly class EnsureForumEventPlaceAccess
{
    public function __construct(private Gate $gate) {}

    public function handle(User $actor, ForumEvent $event): PlaceAccessGrant
    {
        $this->gate->forUser($actor)->authorize('viewAccessDetails', $event);

        return DB::transaction(function () use ($actor, $event): PlaceAccessGrant {
            $lockedEvent = ForumEvent::query()->lockForUpdate()->findOrFail($event->id);
            $lockedActor = User::query()->lockForUpdate()->findOrFail($actor->id);
            $this->gate->forUser($lockedActor)->authorize('viewAccessDetails', $lockedEvent);
            $registration = $lockedEvent->registrationFor($lockedActor);

            if ($lockedEvent->place_id === null
                || $registration === null
                || ! $lockedEvent->canDiscloseAccessTo($lockedActor)
            ) {
                throw new AuthorizationException;
            }

            $place = Place::query()->lockForUpdate()->findOrFail($lockedEvent->place_id);

            if ($place->status !== PlaceStatus::Active || $place->archived_at !== null) {
                throw new AuthorizationException;
            }

            $validFrom = now()->toImmutable()->subMinute();
            $maximumValidUntil = $validFrom->addDays(30);
            $eventValidUntil = $lockedEvent->ends_at->addDay();
            $validUntil = $eventValidUntil->lessThan($maximumValidUntil)
                ? $eventValidUntil
                : $maximumValidUntil;

            if (! $validUntil->isAfter($validFrom)) {
                throw new AuthorizationException;
            }

            $idempotencyKey = hash('sha256', implode('|', [
                'meetup-place-attendance',
                (string) $lockedEvent->id,
                (string) $registration->id,
                $validFrom->toDateString(),
            ]));

            return PlaceAccessGrant::query()->createOrFirst(
                ['idempotency_key' => $idempotencyKey],
                [
                    'place_id' => $place->id,
                    'user_id' => $lockedActor->id,
                    'event_id' => $lockedEvent->id,
                    'issued_by_user_id' => $lockedEvent->organizer_user_id,
                    'purpose' => PlaceAccessPurpose::EventAttendance,
                    'may_view_exact_location' => true,
                    'valid_from' => $validFrom,
                    'valid_until' => $validUntil,
                ],
            );
        }, 3);
    }
}
