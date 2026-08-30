<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\PlaceExactLocationRevealContext;
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
            $locked = Place::query()->lockForUpdate()->findOrFail($place->id);
            $this->gate->forUser($actor)->authorize('viewExactLocation', $locked);
            $isManager = $locked->isManagedBy($actor);
            $grantQuery = $locked->activeExactGrantsFor($actor);

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

            if ($grant !== null && in_array($grant->purpose, [
                PlaceAccessPurpose::EventAttendance,
                PlaceAccessPurpose::EventOperations,
            ], true)) {
                $event = $grant->event_id === null
                    ? null
                    : ForumEvent::query()->lockForUpdate()->find($grant->event_id);

                if (! $event instanceof ForumEvent
                    || $event->place_id !== $locked->id
                    || ($context->eventId !== null && $event->id !== $context->eventId)
                ) {
                    throw new AuthorizationException;
                }

                if ($grant->purpose === PlaceAccessPurpose::EventAttendance
                    && ! $event->canDiscloseAccessTo($actor)
                ) {
                    throw new AuthorizationException;
                }

                if ($grant->purpose === PlaceAccessPurpose::EventOperations
                    && ! $this->gate->forUser($actor)->allows('update', $event)
                ) {
                    throw new AuthorizationException;
                }
            }

            PlaceAccessAudit::query()->create([
                'place_id' => $locked->id,
                'user_id' => $actor->id,
                'place_access_grant_id' => $grant?->id,
                'event_id' => $grant?->event_id,
                'event_type' => 'exact-location-viewed',
                'purpose' => $grant?->purpose->value,
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
