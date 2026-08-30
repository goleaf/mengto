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

final readonly class ReviewPlaceMedia
{
    public function __construct(private Gate $gate, private Repository $config) {}

    public function handle(
        User $actor,
        Place $place,
        PlaceMedia $media,
        bool $approved,
        string $reasonCode,
        string $idempotencyKey,
    ): PlaceMedia {
        $this->gate->forUser($actor)->authorize('moderateMedia', $place);
        Validator::make(compact('reasonCode', 'idempotencyKey'), [
            'reasonCode' => ['required', 'string', 'max:120', 'regex:/^[a-z0-9-]+$/'],
            'idempotencyKey' => ['required', 'string', 'min:16', 'max:190'],
        ])->validate();
        $operationKey = hash('sha256', "place-media-review|{$media->id}|{$actor->id}|{$idempotencyKey}");
        $fingerprint = hash('sha256', ($approved ? 'approved' : 'rejected').'|'.$reasonCode);

        return DB::transaction(function () use (
            $actor, $approved, $fingerprint, $media, $operationKey, $place, $reasonCode,
        ): PlaceMedia {
            $lockedPlace = Place::query()->with('organization.activeMemberships')
                ->lockForUpdate()->findOrFail($place->id);
            $locked = PlaceMedia::query()->lockForUpdate()->findOrFail($media->id);
            $this->gate->forUser($actor)->authorize('moderateMedia', $lockedPlace);
            $this->assertParent($locked, $lockedPlace);
            $event = PlaceMediaEvent::query()->where('idempotency_key', $operationKey)->first();

            if ($event instanceof PlaceMediaEvent) {
                if (($event->metadata['fingerprint'] ?? null) !== $fingerprint) {
                    $this->conflict();
                }

                return $locked;
            }

            if ($locked->status !== PlaceMediaStatus::PendingReview) {
                throw ValidationException::withMessages([
                    'media' => __('places.media.validation.not_pending'),
                ]);
            }

            $status = $approved ? PlaceMediaStatus::Active : PlaceMediaStatus::Rejected;
            $feature = $approved && ! PlaceMedia::query()
                ->where('place_id', $lockedPlace->id)
                ->where('status', PlaceMediaStatus::Active->value)
                ->where('is_featured', true)
                ->exists();
            $locked->forceFill([
                'status' => $status,
                'moderated_by_user_id' => $actor->id,
                'moderation_reason_code' => $reasonCode,
                'moderated_at' => now(),
                'is_featured' => $feature,
                'featured_key' => $feature ? "featured:{$lockedPlace->id}" : null,
                'recoverable_until' => $approved
                    ? null
                    : now()->addDays($this->config->integer('images.place_uploads.recovery_days')),
                'retained_until' => $approved
                    ? null
                    : now()->addDays($this->config->integer('images.place_uploads.recovery_days')),
                'lock_version' => $locked->lock_version + 1,
            ])->save();

            PlaceMediaEvent::query()->create([
                'place_media_id' => $locked->id,
                'actor_user_id' => $actor->id,
                'event_type' => $approved ? 'approved' : 'rejected',
                'reason_code' => $reasonCode,
                'idempotency_key' => $operationKey,
                'metadata' => ['fingerprint' => $fingerprint],
                'created_at' => now(),
            ]);

            return $locked->refresh();
        }, 3);
    }

    private function assertParent(PlaceMedia $media, Place $place): void
    {
        if ($media->place_id !== $place->id) {
            abort(404);
        }
    }

    private function conflict(): never
    {
        throw ValidationException::withMessages([
            'idempotency_key' => __('places.validation.idempotency_conflict'),
        ]);
    }
}
