<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\PlaceExactLocationRevealContext;
use App\Enums\ForumEventStatus;
use App\Enums\PlaceAccessPurpose;
use App\Models\ForumEvent;
use App\Models\Place;
use App\Models\PlaceAccessAudit;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

final readonly class RevealPlaceExactLocation
{
    public function __construct(private Gate $gate) {}

    /** @return array{address: string|null, latitude: string|null, longitude: string|null, instructions: string|null} */
    public function handle(
        User $actor,
        Place $place,
        string|PlaceExactLocationRevealContext $channel,
    ): array
    {
        $this->gate->forUser($actor)->authorize('viewExactLocation', $place);
        $context = $channel instanceof PlaceExactLocationRevealContext
            ? $channel
            : new PlaceExactLocationRevealContext(null, null, $channel);
        Validator::make([
            'channel' => $context->channel,
            'event_id' => $context->eventId,
            'purpose' => $context->purpose?->value,
        ], [
            'channel' => ['required', 'string', 'max:80', 'regex:/^[a-z0-9-]+$/'],
            'event_id' => ['nullable', 'integer', 'min:1'],
            'purpose' => ['nullable', 'string', 'max:50'],
        ])->validate();

        return DB::transaction(function () use ($actor, $place, $context): array {
            $lockedActor = User::query()->lockForUpdate()->findOrFail($actor->id);
            $locked = Place::query()->lockForUpdate()->findOrFail($place->id);
            $isManager = $locked->isManagedBy($lockedActor);
            $grantQuery = $locked->activeExactGrantsFor($lockedActor);

            if ($context->purpose !== null) {
                $grantQuery->where('purpose', $context->purpose->value);
            }

            if ($context->eventId !== null) {
                $grantQuery->where('event_id', $context->eventId);
            }

            $grant = $isManager ? null : $grantQuery->lockForUpdate()->first();

            if (! $isManager && $grant === null) {
                throw new AuthorizationException;
            }

            if ($grant !== null && $context->purpose !== null && $grant->purpose !== $context->purpose) {
                throw new AuthorizationException;
            }

            $eventPurpose = $context->purpose ?? $grant?->purpose;
            $eventId = $context->eventId ?? $grant?->event_id;

            if (in_array($eventPurpose, [
                PlaceAccessPurpose::EventAttendance,
                PlaceAccessPurpose::EventOperations,
            ], true)) {
                $event = $eventId === null
                    ? null
                    : ForumEvent::query()->lockForUpdate()->find($eventId);

                if (! $event instanceof ForumEvent
                    || $event->place_id !== $locked->id
                    || ($context->eventId !== null && $event->id !== $context->eventId)
                ) {
                    throw new AuthorizationException;
                }

                if ($eventPurpose === PlaceAccessPurpose::EventAttendance) {
                    $this->gate->forUser($lockedActor)->authorize('viewAccessDetails', $event);
                }

                if ($eventPurpose === PlaceAccessPurpose::EventOperations) {
                    if ($event->status === ForumEventStatus::Cancelled || $event->hasEnded()) {
                        throw new AuthorizationException;
                    }

                    $this->gate->forUser($lockedActor)->authorize('update', $event);
                }
            } else {
                $this->gate->forUser($lockedActor)->authorize('viewExactLocation', $locked);
            }

            PlaceAccessAudit::query()->create([
                'place_id' => $locked->id,
                'user_id' => $lockedActor->id,
                'place_access_grant_id' => $grant?->id,
                'event_id' => $eventId,
                'event_type' => 'exact-location-viewed',
                'purpose' => $eventPurpose?->value,
                'channel' => $context->channel,
                'created_at' => now(),
            ]);

            return [
                'address' => $locked->exact_address,
                'latitude' => $locked->exact_latitude,
                'longitude' => $locked->exact_longitude,
                'instructions' => $locked->private_instructions,
            ];
        }, 3);
    }
}
