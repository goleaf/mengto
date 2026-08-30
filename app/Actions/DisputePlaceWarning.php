<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\PlaceWarningDisputeStatus;
use App\Enums\PlaceWarningStatus;
use App\Models\PlaceWarning;
use App\Models\PlaceWarningDispute;
use App\Models\PlaceWarningEvent;
use App\Models\User;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final readonly class DisputePlaceWarning
{
    public function __construct(private Gate $gate) {}

    public function handle(
        User $actor,
        PlaceWarning $warning,
        string $reason,
        ?string $evidence,
        string $idempotencyKey,
    ): PlaceWarningDispute {
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
        $this->gate->forUser($actor)->authorize('dispute', $warning);

        $replay = PlaceWarningDispute::query()
            ->where('disputant_user_id', $actor->id)
            ->where('idempotency_key', $validated['idempotency_key'])
            ->first();
        if ($replay !== null) {
            return $this->validatedReplay($replay, $actor, $warning, $validated);
        }

        return DB::transaction(function () use ($actor, $warning, $validated): PlaceWarningDispute {
            $locked = PlaceWarning::query()->with('place')->lockForUpdate()->findOrFail($warning->id);
            $this->gate->forUser($actor)->authorize('dispute', $locked);
            $dispute = PlaceWarningDispute::query()->createOrFirst(
                ['disputant_user_id' => $actor->id, 'idempotency_key' => $validated['idempotency_key']],
                [
                    'place_warning_id' => $locked->id,
                    'reason' => $validated['reason'],
                    'evidence' => $validated['evidence'],
                    'status' => PlaceWarningDisputeStatus::Submitted,
                ],
            );
            if (! $dispute->wasRecentlyCreated) {
                return $this->validatedReplay($dispute, $actor, $locked, $validated);
            }

            $from = $locked->status;
            $locked->forceFill([
                'status' => PlaceWarningStatus::Disputed,
                'disputed_at' => now(),
                'lock_version' => $locked->lock_version + 1,
            ])->save();
            PlaceWarningEvent::query()->createOrFirst(
                ['actor_user_id' => $actor->id, 'idempotency_key' => 'disputed:'.$validated['idempotency_key']],
                [
                    'place_warning_id' => $locked->id,
                    'event_type' => 'disputed',
                    'from_status' => $from->value,
                    'to_status' => PlaceWarningStatus::Disputed->value,
                    'public_summary_key' => 'places.warnings.events.disputed',
                ],
            );

            return $dispute;
        }, 3);
    }

    /** @param array{reason: string, evidence: string|null, idempotency_key: string} $validated */
    private function validatedReplay(
        PlaceWarningDispute $dispute,
        User $actor,
        PlaceWarning $warning,
        array $validated,
    ): PlaceWarningDispute {
        if (
            $dispute->disputant_user_id !== $actor->id
            || $dispute->place_warning_id !== $warning->id
            || $dispute->reason !== $validated['reason']
            || $dispute->evidence !== $validated['evidence']
        ) {
            throw ValidationException::withMessages([
                'place_idempotency_key' => __('places.validation.warning_idempotency_conflict'),
            ]);
        }

        return $dispute;
    }
}
