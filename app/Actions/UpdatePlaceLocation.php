<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\UpdatePlaceLocationData;
use App\Enums\PlaceAccessGrantStatus;
use App\Models\Place;
use App\Models\PlaceLocationVersion;
use App\Models\User;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

final readonly class UpdatePlaceLocation
{
    public function __construct(private Gate $gate) {}

    public function handle(User $actor, Place $place, UpdatePlaceLocationData $data): Place
    {
        $this->gate->forUser($actor)->authorize('update', $place);
        Validator::make([
            'publicRegion' => $data->publicRegion,
            'publicAddress' => $data->publicAddress,
            'publicLatitude' => $data->publicLatitude,
            'publicLongitude' => $data->publicLongitude,
            'exactAddress' => $data->exactAddress,
            'exactLatitude' => $data->exactLatitude,
            'exactLongitude' => $data->exactLongitude,
            'privateInstructions' => $data->privateInstructions,
            'reasonCode' => $data->reasonCode,
        ], [
            'publicRegion' => ['required', 'string', 'max:160'],
            'publicAddress' => ['nullable', 'string', 'max:500'],
            'publicLatitude' => ['nullable', 'numeric', 'between:-90,90', 'required_with:publicLongitude'],
            'publicLongitude' => ['nullable', 'numeric', 'between:-180,180', 'required_with:publicLatitude'],
            'exactAddress' => ['nullable', 'string', 'max:2000'],
            'exactLatitude' => ['nullable', 'numeric', 'between:-90,90', 'required_with:exactLongitude'],
            'exactLongitude' => ['nullable', 'numeric', 'between:-180,180', 'required_with:exactLatitude'],
            'privateInstructions' => ['nullable', 'string', 'max:3000'],
            'reasonCode' => ['required', 'string', 'max:120', 'regex:/^[a-z0-9-]+$/'],
        ])->validate();

        return DB::transaction(function () use ($actor, $place, $data): Place {
            $locked = Place::query()->lockForUpdate()->findOrFail($place->id);
            $this->gate->forUser($actor)->authorize('update', $locked);
            $version = $locked->locationVersions()->max('version') + 1;

            PlaceLocationVersion::query()->create([
                'place_id' => $locked->id,
                'changed_by_user_id' => $actor->id,
                'version' => $version,
                'public_region' => $locked->public_region,
                'public_address' => $locked->public_address,
                'public_latitude' => $locked->public_latitude,
                'public_longitude' => $locked->public_longitude,
                'exact_address' => $locked->exact_address,
                'exact_latitude' => $locked->exact_latitude,
                'exact_longitude' => $locked->exact_longitude,
                'private_instructions' => $locked->private_instructions,
                'reason_code' => $data->reasonCode,
                'created_at' => now(),
            ]);

            $locked->forceFill([
                'public_region' => trim($data->publicRegion),
                'public_address' => $data->publicAddress,
                'public_latitude' => $data->publicLatitude,
                'public_longitude' => $data->publicLongitude,
                'exact_address' => $data->exactAddress,
                'exact_latitude' => $data->exactLatitude,
                'exact_longitude' => $data->exactLongitude,
                'private_instructions' => $data->privateInstructions,
                'last_edited_by_user_id' => $actor->id,
                'lock_version' => $locked->lock_version + 1,
            ])->save();

            $locked->accessGrants()
                ->where('status', PlaceAccessGrantStatus::Active->value)
                ->whereNull('revoked_at')
                ->update([
                    'status' => PlaceAccessGrantStatus::Revoked->value,
                    'revoked_by_user_id' => $actor->id,
                    'revoked_at' => now(),
                    'revocation_reason_code' => 'location-changed',
                    'updated_at' => now(),
                ]);

            return $locked->refresh();
        }, 3);
    }
}
