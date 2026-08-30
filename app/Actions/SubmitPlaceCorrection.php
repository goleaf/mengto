<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\PlaceCorrectionField;
use App\Enums\PlaceCorrectionSource;
use App\Enums\PlaceCorrectionStatus;
use App\Models\Place;
use App\Models\PlaceCorrection;
use App\Models\PlaceCorrectionEvent;
use App\Models\User;
use App\Services\PlaceContributionNotifier;
use Carbon\CarbonInterface;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final readonly class SubmitPlaceCorrection
{
    public function __construct(private Gate $gate, private PlaceContributionNotifier $notifier) {}

    public function handle(
        User $actor,
        Place $place,
        PlaceCorrectionField $field,
        ?string $proposedValue,
        string $explanation,
        ?string $evidence,
        PlaceCorrectionSource $source,
        ?CarbonInterface $observedAt,
        string $idempotencyKey,
    ): PlaceCorrection {
        $this->gate->forUser($actor)->authorize('submit', [PlaceCorrection::class, $place]);

        $validated = validator([
            'proposed_value' => is_string($proposedValue) ? trim($proposedValue) : null,
            'explanation' => trim($explanation),
            'evidence' => is_string($evidence) ? trim($evidence) : null,
            'observed_at' => $observedAt,
            'idempotency_key' => $idempotencyKey,
        ], [
            'proposed_value' => $field->proposedValueRules(),
            'explanation' => ['required', 'string', 'min:10', 'max:3000'],
            'evidence' => ['nullable', 'string', 'max:5000'],
            'observed_at' => ['nullable', 'date', 'before_or_equal:now'],
            'idempotency_key' => ['required', 'uuid'],
        ])->validate();

        /** @var array{proposed_value: string|null, explanation: string, evidence: string|null, observed_at: CarbonInterface|null, idempotency_key: string} $validated */
        return DB::transaction(function () use ($actor, $place, $field, $source, $validated): PlaceCorrection {
            $lockedPlace = Place::query()
                ->with('organization')
                ->lockForUpdate()
                ->findOrFail($place->id);
            $this->gate->forUser($actor)->authorize('submit', [PlaceCorrection::class, $lockedPlace]);

            $existing = PlaceCorrection::query()
                ->where('submitter_user_id', $actor->id)
                ->where('idempotency_key', $validated['idempotency_key'])
                ->first();

            if ($existing !== null) {
                return $this->validatedReplay($existing, $lockedPlace, $field, $source, $validated);
            }

            if (PlaceCorrection::query()
                ->where('submitter_user_id', $actor->id)
                ->where('created_at', '>=', now()->subHour())
                ->count() >= 5) {
                throw ValidationException::withMessages([
                    'place_correction' => __('places.validation.rate_limited'),
                ]);
            }

            $fingerprint = hash('sha256', json_encode([
                'place_id' => $lockedPlace->id,
                'field' => $field->value,
                'proposed_value' => $validated['proposed_value'],
            ], JSON_THROW_ON_ERROR));

            $equivalent = PlaceCorrection::query()
                ->open()
                ->where('pending_fingerprint', $fingerprint)
                ->lockForUpdate()
                ->first();

            if ($equivalent !== null) {
                PlaceCorrectionEvent::query()->create([
                    'place_correction_id' => $equivalent->id,
                    'actor_user_id' => $actor->id,
                    'idempotency_key' => $validated['idempotency_key'],
                    'event_type' => 'supporting_evidence_submitted',
                    'from_status' => $equivalent->moderation_status,
                    'to_status' => $equivalent->moderation_status,
                    'public_summary_key' => 'places.corrections.history.supporting_evidence_submitted',
                    'metadata' => [
                        'source' => $source->value,
                        'observed_at' => $validated['observed_at']?->toIso8601String(),
                        'evidence' => $validated['evidence'],
                        'explanation' => $validated['explanation'],
                    ],
                ]);

                return $equivalent;
            }

            $originalValue = $lockedPlace->getAttribute($field->placeColumn());
            $correction = PlaceCorrection::query()->create([
                'place_id' => $lockedPlace->id,
                'submitter_user_id' => $actor->id,
                'stable_key' => 'place-correction-'.Str::lower((string) Str::ulid()),
                'idempotency_key' => $validated['idempotency_key'],
                'correction_field' => $field,
                'original_value' => is_scalar($originalValue) ? (string) $originalValue : null,
                'original_version' => $lockedPlace->lock_version,
                'proposed_value' => $validated['proposed_value'],
                'explanation' => $validated['explanation'],
                'evidence' => $validated['evidence'],
                'source' => $source,
                'observed_at' => $validated['observed_at'],
                'moderation_status' => PlaceCorrectionStatus::Pending,
                'pending_fingerprint' => $fingerprint,
            ]);

            PlaceCorrectionEvent::query()->create([
                'place_correction_id' => $correction->id,
                'actor_user_id' => $actor->id,
                'idempotency_key' => $validated['idempotency_key'],
                'event_type' => 'submitted',
                'from_status' => null,
                'to_status' => PlaceCorrectionStatus::Pending,
                'public_summary_key' => 'places.corrections.history.submitted',
                'metadata' => [
                    'source' => $source->value,
                    'observed_at' => $validated['observed_at']?->toIso8601String(),
                ],
            ]);

            if ($lockedPlace->owner_user_id !== null && $lockedPlace->owner_user_id !== $actor->id) {
                $recipient = User::query()->find($lockedPlace->owner_user_id);
                if ($recipient !== null) {
                    DB::afterCommit(fn () => $this->notifier->send(
                        $recipient,
                        'place_correction_submitted',
                        'places.notifications.correction_submitted_title',
                        'places.notifications.correction_submitted_body',
                        'place-correction-submitted:'.$correction->stable_key.':'.$recipient->id,
                        ['place' => $lockedPlace->name],
                    ));
                }
            }

            return $correction;
        }, 3);
    }

    /**
     * @param  array{proposed_value: string|null, explanation: string, evidence: string|null, observed_at: CarbonInterface|null, idempotency_key: string}  $validated
     */
    private function validatedReplay(
        PlaceCorrection $correction,
        Place $place,
        PlaceCorrectionField $field,
        PlaceCorrectionSource $source,
        array $validated,
    ): PlaceCorrection {
        if (
            $correction->place_id !== $place->id
            || $correction->correction_field !== $field
            || $correction->proposed_value !== $validated['proposed_value']
            || $correction->explanation !== $validated['explanation']
            || $correction->source !== $source
            || $correction->observed_at?->toIso8601String() !== $validated['observed_at']?->toIso8601String()
        ) {
            throw ValidationException::withMessages([
                'place_idempotency_key' => __('places.validation.correction_idempotency_conflict'),
            ]);
        }

        return $correction;
    }
}
