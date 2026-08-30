<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Place;
use App\Models\PlaceMedia;
use App\Models\PlaceMediaEvent;
use App\Models\User;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

final readonly class ReorderPlaceMedia
{
    public function __construct(private Gate $gate) {}

    /** @param list<string> $orderedMediaKeys */
    public function handle(
        User $actor,
        Place $place,
        array $orderedMediaKeys,
        string $idempotencyKey,
    ): void {
        $this->gate->forUser($actor)->authorize('manageMedia', $place);
        Validator::make([
            'keys' => $orderedMediaKeys,
            'idempotency_key' => $idempotencyKey,
        ], [
            'keys' => ['required', 'array', 'min:1', 'max:10'],
            'keys.*' => ['required', 'string', 'distinct', 'max:26'],
            'idempotency_key' => ['required', 'string', 'min:16', 'max:190'],
        ])->validate();
        $operationKey = hash('sha256', "place-media-reorder|{$place->id}|{$actor->id}|{$idempotencyKey}");
        $fingerprint = hash('sha256', implode('|', $orderedMediaKeys));

        DB::transaction(function () use (
            $actor, $fingerprint, $idempotencyKey, $operationKey, $orderedMediaKeys, $place,
        ): void {
            $lockedPlace = Place::query()->with('organization.activeMemberships')
                ->lockForUpdate()->findOrFail($place->id);
            $this->gate->forUser($actor)->authorize('manageMedia', $lockedPlace);
            $media = PlaceMedia::query()
                ->where('place_id', $lockedPlace->id)
                ->lockForUpdate()
                ->orderBy('id')
                ->get();

            if ($media->pluck('media_key')->sort()->values()->all()
                !== collect($orderedMediaKeys)->sort()->values()->all()
            ) {
                throw ValidationException::withMessages([
                    'keys' => __('places.media.validation.complete_order'),
                ]);
            }

            $first = $media->firstWhere('media_key', $orderedMediaKeys[0]);
            $existing = PlaceMediaEvent::query()->where('idempotency_key', $operationKey)->first();

            if ($existing instanceof PlaceMediaEvent) {
                if (($existing->metadata['fingerprint'] ?? null) !== $fingerprint) {
                    throw ValidationException::withMessages([
                        'idempotency_key' => __('places.validation.idempotency_conflict'),
                    ]);
                }

                return;
            }

            foreach ($orderedMediaKeys as $index => $mediaKey) {
                $row = $media->firstWhere('media_key', $mediaKey);

                if (! $row instanceof PlaceMedia) {
                    throw ValidationException::withMessages([
                        'keys' => __('places.media.validation.complete_order'),
                    ]);
                }

                $row->forceFill([
                    'position' => $index + 1,
                    'lock_version' => $row->lock_version + 1,
                ])->save();
            }

            PlaceMediaEvent::query()->create([
                'place_media_id' => $first->id,
                'actor_user_id' => $actor->id,
                'event_type' => 'reordered',
                'idempotency_key' => $operationKey,
                'metadata' => ['fingerprint' => $fingerprint, 'count' => count($orderedMediaKeys)],
                'created_at' => now(),
            ]);
        }, 3);
    }
}
