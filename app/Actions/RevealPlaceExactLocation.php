<?php

declare(strict_types=1);

namespace App\Actions;

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
    public function handle(User $actor, Place $place, string $channel): array
    {
        $this->gate->forUser($actor)->authorize('viewExactLocation', $place);
        Validator::make(['channel' => $channel], [
            'channel' => ['required', 'string', 'max:80', 'regex:/^[a-z0-9-]+$/'],
        ])->validate();

        return DB::transaction(function () use ($actor, $place, $channel): array {
            $locked = Place::query()->lockForUpdate()->findOrFail($place->id);
            $this->gate->forUser($actor)->authorize('viewExactLocation', $locked);
            $isManager = $locked->isManagedBy($actor);
            $grant = $isManager
                ? null
                : $locked->activeExactGrantsFor($actor)->lockForUpdate()->first();

            if (! $isManager && $grant === null) {
                throw new AuthorizationException;
            }

            PlaceAccessAudit::query()->create([
                'place_id' => $locked->id,
                'user_id' => $actor->id,
                'place_access_grant_id' => $grant?->id,
                'event_id' => $grant?->event_id,
                'event_type' => 'exact-location-viewed',
                'purpose' => $grant?->purpose->value,
                'channel' => $channel,
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
