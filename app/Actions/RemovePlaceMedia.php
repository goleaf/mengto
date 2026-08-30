<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\PlaceMediaStatus;
use App\Models\Place;
use App\Models\PlaceMedia;
use App\Models\PlaceMediaEvent;
use App\Models\User;
use Illuminate\Config\Repository;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

final readonly class RemovePlaceMedia
{
    public function __construct(private Gate $gate, private Repository $config) {}

    public function handle(
        User $actor,
        Place $place,
        PlaceMedia $media,
        string $reasonCode,
        string $idempotencyKey,
    ): PlaceMedia {
        $this->gate->forUser($actor)->authorize('manageMedia', $place);
        Validator::make(compact('reasonCode', 'idempotencyKey'), [
            'reasonCode' => ['required', 'string', 'max:120', 'regex:/^[a-z0-9-]+$/'],
            'idempotencyKey' => ['required', 'string', 'min:16', 'max:190'],
        ])->validate();
        $operationKey = hash('sha256', "place-media-remove|{$media->id}|{$actor->id}|{$idempotencyKey}");

        return DB::transaction(function () use (
            $actor, $media, $operationKey, $place, $reasonCode,
        ): PlaceMedia {
            $lockedPlace = Place::query()->with('organization.activeMemberships')
                ->lockForUpdate()->findOrFail($place->id);
            $locked = PlaceMedia::query()->lockForUpdate()->findOrFail($media->id);
            $this->gate->forUser($actor)->authorize('manageMedia', $lockedPlace);

            if ($locked->place_id !== $lockedPlace->id) {
                abort(404);
            }

            $event = PlaceMediaEvent::query()->where('idempotency_key', $operationKey)->first();

            if ($event instanceof PlaceMediaEvent) {
                if ($event->reason_code !== $reasonCode) {
                    throw ValidationException::withMessages([
                        'idempotency_key' => __('places.validation.idempotency_conflict'),
                    ]);
                }

                return $locked;
            }

            $recovery = now()->addDays($this->config->integer('images.place_uploads.recovery_days'));
            $locked->forceFill([
                'status' => PlaceMediaStatus::Removed,
                'is_featured' => false,
                'featured_key' => null,
                'removed_at' => now(),
                'recoverable_until' => $recovery,
                'retained_until' => $recovery,
                'lock_version' => $locked->lock_version + 1,
            ])->save();

            PlaceMediaEvent::query()->create([
                'place_media_id' => $locked->id,
                'actor_user_id' => $actor->id,
                'event_type' => 'removed',
                'reason_code' => $reasonCode,
                'idempotency_key' => $operationKey,
                'created_at' => now(),
            ]);

            return $locked->refresh();
        }, 3);
    }
}
