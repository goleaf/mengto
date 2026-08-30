<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\PlaceWarning;
use App\Models\PlaceWarningConfirmation;
use App\Models\PlaceWarningEvent;
use App\Models\User;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final readonly class ConfirmPlaceWarning
{
    public function __construct(private Gate $gate) {}

    public function handle(User $actor, PlaceWarning $warning, string $idempotencyKey): PlaceWarningConfirmation
    {
        validator(['idempotency_key' => $idempotencyKey], ['idempotency_key' => ['required', 'uuid']])->validate();
        $warning->loadMissing('place');
        $this->gate->forUser($actor)->authorize('confirm', $warning);

        $replay = PlaceWarningConfirmation::query()
            ->where('user_id', $actor->id)
            ->where('idempotency_key', $idempotencyKey)
            ->first();
        if ($replay !== null) {
            return $this->validatedReplay($replay, $actor, $warning);
        }

        return DB::transaction(function () use ($actor, $warning, $idempotencyKey): PlaceWarningConfirmation {
            $locked = PlaceWarning::query()->with('place')->lockForUpdate()->findOrFail($warning->id);
            $this->gate->forUser($actor)->authorize('confirm', $locked);

            $existing = PlaceWarningConfirmation::query()
                ->where('place_warning_id', $locked->id)
                ->where('user_id', $actor->id)
                ->first();
            if ($existing !== null) {
                if ($existing->idempotency_key !== $idempotencyKey) {
                    throw ValidationException::withMessages([
                        'place_warning' => __('places.validation.warning_already_confirmed'),
                    ]);
                }

                return $existing;
            }

            $confirmation = PlaceWarningConfirmation::query()->createOrFirst(
                ['user_id' => $actor->id, 'idempotency_key' => $idempotencyKey],
                [
                    'place_warning_id' => $locked->id,
                    'confirmed_at' => now(),
                ],
            );

            if (! $confirmation->wasRecentlyCreated) {
                return $this->validatedReplay($confirmation, $actor, $locked);
            }

            PlaceWarningEvent::query()->createOrFirst(
                ['actor_user_id' => $actor->id, 'idempotency_key' => 'confirmed:'.$idempotencyKey],
                [
                    'place_warning_id' => $locked->id,
                    'event_type' => 'confirmed',
                    'from_status' => $locked->status->value,
                    'to_status' => $locked->status->value,
                    'public_summary_key' => 'places.warnings.events.confirmed',
                ],
            );

            return $confirmation;
        }, 3);
    }

    private function validatedReplay(
        PlaceWarningConfirmation $confirmation,
        User $actor,
        PlaceWarning $warning,
    ): PlaceWarningConfirmation {
        if ($confirmation->user_id !== $actor->id || $confirmation->place_warning_id !== $warning->id) {
            throw ValidationException::withMessages([
                'place_idempotency_key' => __('places.validation.warning_idempotency_conflict'),
            ]);
        }

        return $confirmation;
    }
}
