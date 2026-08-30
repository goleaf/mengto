<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\PlaceWarningAppealStatus;
use App\Models\PlaceWarning;
use App\Models\PlaceWarningAppeal;
use App\Models\PlaceWarningEvent;
use App\Models\User;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final readonly class AppealPlaceWarning
{
    public function __construct(private Gate $gate) {}

    public function handle(
        User $actor,
        PlaceWarning $warning,
        string $reason,
        ?string $evidence,
        string $idempotencyKey,
    ): PlaceWarningAppeal {
        /** @var array{reason: string, evidence: string|null, idempotency_key: string} $validated */
        $validated = validator([
            'reason' => trim($reason),
            'evidence' => $evidence === null ? null : trim($evidence),
            'idempotency_key' => $idempotencyKey,
        ], [
            'reason' => ['required', 'string', 'min:10', 'max:4000'],
            'evidence' => ['nullable', 'string', 'min:5', 'max:4000'],
            'idempotency_key' => ['required', 'uuid'],
        ])->validate();
        $warning->loadMissing('place');
        $this->gate->forUser($actor)->authorize('appeal', $warning);

        $replay = PlaceWarningAppeal::query()
            ->where('appellant_user_id', $actor->id)
            ->where('idempotency_key', $validated['idempotency_key'])
            ->first();
        if ($replay !== null) {
            return $this->validatedReplay($replay, $actor, $warning, $validated);
        }

        return DB::transaction(function () use ($actor, $warning, $validated): PlaceWarningAppeal {
            $locked = PlaceWarning::query()->with('place')->lockForUpdate()->findOrFail($warning->id);
            $this->gate->forUser($actor)->authorize('appeal', $locked);
            $appeal = PlaceWarningAppeal::query()->createOrFirst(
                ['appellant_user_id' => $actor->id, 'idempotency_key' => $validated['idempotency_key']],
                [
                    'place_warning_id' => $locked->id,
                    'reason' => $validated['reason'],
                    'evidence' => $validated['evidence'],
                    'status' => PlaceWarningAppealStatus::Submitted,
                ],
            );
            if (! $appeal->wasRecentlyCreated) {
                return $this->validatedReplay($appeal, $actor, $locked, $validated);
            }

            PlaceWarningEvent::query()->createOrFirst(
                ['actor_user_id' => $actor->id, 'idempotency_key' => 'appealed:'.$validated['idempotency_key']],
                [
                    'place_warning_id' => $locked->id,
                    'event_type' => 'appealed',
                    'from_status' => $locked->status->value,
                    'to_status' => $locked->status->value,
                    'public_summary_key' => 'places.warnings.events.appealed',
                ],
            );

            return $appeal;
        }, 3);
    }

    /** @param array{reason: string, evidence: string|null, idempotency_key: string} $validated */
    private function validatedReplay(
        PlaceWarningAppeal $appeal,
        User $actor,
        PlaceWarning $warning,
        array $validated,
    ): PlaceWarningAppeal {
        if (
            $appeal->appellant_user_id !== $actor->id
            || $appeal->place_warning_id !== $warning->id
            || $appeal->reason !== $validated['reason']
            || $appeal->evidence !== $validated['evidence']
        ) {
            throw ValidationException::withMessages([
                'place_idempotency_key' => __('places.validation.warning_idempotency_conflict'),
            ]);
        }

        return $appeal;
    }
}
