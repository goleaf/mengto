<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\PlaceCorrectionField;
use App\Enums\PlaceCorrectionResolution;
use App\Enums\PlaceCorrectionStatus;
use App\Models\Place;
use App\Models\PlaceCorrection;
use App\Models\PlaceCorrectionEvent;
use App\Models\User;
use App\Services\PlaceIdentityNormalizer;
use App\Services\PlaceContributionNotifier;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final readonly class ReviewPlaceCorrection
{
    public function __construct(
        private Gate $gate,
        private PlaceIdentityNormalizer $normalizer,
        private PlaceContributionNotifier $notifier,
    ) {}

    public function handle(
        User $actor,
        PlaceCorrection $correction,
        PlaceCorrectionStatus $decision,
        string $decisionReason,
        string $idempotencyKey,
        bool $acceptStaleMerge = false,
        ?string $appliedValue = null,
    ): PlaceCorrection {
        $correction->loadMissing('place.organization');
        $this->gate->forUser($actor)->authorize('review', $correction);

        if (! $decision->isReviewDecision()) {
            throw ValidationException::withMessages([
                'moderation_status' => __('places.validation.correction_decision_unavailable'),
            ]);
        }

        $validated = validator([
            'decision_reason' => trim($decisionReason),
            'idempotency_key' => $idempotencyKey,
            'accept_stale_merge' => $acceptStaleMerge,
            'applied_value' => is_string($appliedValue) ? trim($appliedValue) : null,
        ], [
            'decision_reason' => ['required', 'string', 'min:10', 'max:3000'],
            'idempotency_key' => ['required', 'uuid'],
            'accept_stale_merge' => ['boolean'],
            'applied_value' => ['nullable', 'string', 'max:2000'],
        ])->validate();

        /** @var array{decision_reason: string, idempotency_key: string, accept_stale_merge: bool, applied_value: string|null} $validated */
        return DB::transaction(function () use ($actor, $correction, $decision, $validated): PlaceCorrection {
            $existingEvent = PlaceCorrectionEvent::query()
                ->where('actor_user_id', $actor->id)
                ->where('idempotency_key', $validated['idempotency_key'])
                ->first();

            if ($existingEvent !== null) {
                return $this->validatedReplay($existingEvent, $correction, $decision, $validated);
            }

            $lockedCorrection = PlaceCorrection::query()
                ->with('place.organization')
                ->lockForUpdate()
                ->findOrFail($correction->id);
            $this->gate->forUser($actor)->authorize('review', $lockedCorrection);
            $lockedPlace = Place::query()->lockForUpdate()->findOrFail($lockedCorrection->place_id);
            $lockedPlace->load('organization');

            if (! in_array($lockedCorrection->moderation_status, [
                PlaceCorrectionStatus::Pending,
                PlaceCorrectionStatus::InReview,
                PlaceCorrectionStatus::NeedsInformation,
            ], true)) {
                throw ValidationException::withMessages([
                    'place_correction' => __('places.validation.correction_already_resolved'),
                ]);
            }

            $appliedValue = $this->validatedAppliedValue($lockedCorrection, $decision, $validated['applied_value']);
            $stale = $lockedPlace->lock_version !== $lockedCorrection->original_version;

            if ($decision->appliesCanonicalMutation() && $stale && ! $validated['accept_stale_merge']) {
                throw ValidationException::withMessages([
                    'place_correction' => __('places.validation.correction_stale_merge_required'),
                ]);
            }

            $now = now();
            $resolution = $this->resolutionFor($decision);
            $eventType = $decision->value;

            if ($decision->appliesCanonicalMutation()) {
                $this->applyField($lockedPlace, $lockedCorrection->correction_field, $appliedValue);
                $lockedPlace->forceFill([
                    'last_edited_by_user_id' => $actor->id,
                    'lock_version' => $lockedPlace->lock_version + 1,
                ])->save();

                if ($stale) {
                    $eventType = 'accepted_after_stale_merge';
                }
            }

            $lockedCorrection->forceFill([
                'reviewer_user_id' => $actor->id,
                'applied_by_user_id' => $decision->appliesCanonicalMutation() ? $actor->id : null,
                'moderation_status' => $decision,
                'resolution' => $resolution,
                'decision_reason' => $validated['decision_reason'],
                'applied_value' => $decision->appliesCanonicalMutation() ? $appliedValue : null,
                'reviewed_at' => $now,
                'applied_at' => $decision->appliesCanonicalMutation() ? $now : null,
                'pending_fingerprint' => $decision->isReviewDecision() && ! in_array($decision, [
                    PlaceCorrectionStatus::InReview,
                    PlaceCorrectionStatus::NeedsInformation,
                ], true) ? null : $lockedCorrection->pending_fingerprint,
                'lock_version' => $lockedCorrection->lock_version + 1,
            ])->save();

            PlaceCorrectionEvent::query()->create([
                'place_correction_id' => $lockedCorrection->id,
                'actor_user_id' => $actor->id,
                'idempotency_key' => $validated['idempotency_key'],
                'event_type' => $eventType,
                'from_status' => $lockedCorrection->getOriginal('moderation_status'),
                'to_status' => $decision,
                'public_summary_key' => 'places.corrections.history.'.$eventType,
                'private_note' => $validated['decision_reason'],
                'metadata' => [
                    'accepted_stale_merge' => $stale && $validated['accept_stale_merge'],
                    'place_lock_version' => $lockedPlace->lock_version,
                ],
            ]);

            $recipient = User::query()->find($lockedCorrection->submitter_user_id);
            if ($recipient !== null && $recipient->id !== $actor->id) {
                DB::afterCommit(fn () => $this->notifier->send(
                    $recipient,
                    'place_correction_reviewed',
                    'places.notifications.correction_reviewed_title',
                    'places.notifications.correction_reviewed_body',
                    'place-correction-reviewed:'.$lockedCorrection->stable_key.':'.$decision->value.':'.$recipient->id,
                    ['place' => $lockedPlace->name, 'status' => $decision->value],
                ));
            }

            return $lockedCorrection->refresh();
        }, 3);
    }

    /** @param array{decision_reason: string, idempotency_key: string, accept_stale_merge: bool, applied_value: string|null} $validated */
    private function validatedReplay(
        PlaceCorrectionEvent $event,
        PlaceCorrection $correction,
        PlaceCorrectionStatus $decision,
        array $validated,
    ): PlaceCorrection {
        if (
            $event->place_correction_id !== $correction->id
            || $event->to_status !== $decision
            || $event->private_note !== $validated['decision_reason']
        ) {
            throw ValidationException::withMessages([
                'place_idempotency_key' => __('places.validation.correction_review_idempotency_conflict'),
            ]);
        }

        return $correction->refresh();
    }

    private function validatedAppliedValue(
        PlaceCorrection $correction,
        PlaceCorrectionStatus $decision,
        ?string $appliedValue,
    ): ?string {
        if (! $decision->appliesCanonicalMutation()) {
            return null;
        }

        $value = $appliedValue ?? $correction->proposed_value;
        $rules = $correction->correction_field->proposedValueRules();
        validator(['applied_value' => $value], ['applied_value' => $rules])->validate();

        return $value;
    }

    private function applyField(Place $place, PlaceCorrectionField $field, ?string $value): void
    {
        $attributes = match ($field) {
            PlaceCorrectionField::Name => [
                'name' => $value,
                'normalized_name' => $this->normalizer->name((string) $value),
            ],
            PlaceCorrectionField::Summary => ['summary' => $value],
            PlaceCorrectionField::PublicAddress => [
                'public_address' => $value,
                'normalized_address' => $this->normalizer->address($value),
            ],
            PlaceCorrectionField::PublicPhone => [
                'public_phone' => $value,
                'normalized_phone' => $this->normalizer->phone($value),
            ],
            PlaceCorrectionField::PublicWebsite => [
                'public_website' => $value,
                'normalized_website' => $this->normalizer->website($value),
            ],
            PlaceCorrectionField::PublicEmail => [
                'public_email' => $value,
                'normalized_email' => $this->normalizer->email($value),
            ],
            PlaceCorrectionField::PetRules => ['pet_rules' => $value],
        };

        $place->forceFill($attributes);
    }

    private function resolutionFor(PlaceCorrectionStatus $decision): ?PlaceCorrectionResolution
    {
        return match ($decision) {
            PlaceCorrectionStatus::Accepted => PlaceCorrectionResolution::Applied,
            PlaceCorrectionStatus::PartiallyAccepted => PlaceCorrectionResolution::PartiallyApplied,
            PlaceCorrectionStatus::Rejected => PlaceCorrectionResolution::NotApplied,
            PlaceCorrectionStatus::Superseded => PlaceCorrectionResolution::Superseded,
            default => null,
        };
    }
}
