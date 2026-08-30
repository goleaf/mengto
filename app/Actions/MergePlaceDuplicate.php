<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\PlaceFactScope;
use App\Enums\PlaceStatus;
use App\Enums\PlaceSubmissionAction;
use App\Enums\PlaceSubmissionResolution;
use App\Enums\PlaceSubmissionStatus;
use App\Models\Place;
use App\Models\PlaceDuplicateCandidate;
use App\Models\PlaceFact;
use App\Models\PlaceMergeRedirect;
use App\Models\PlaceSubmission;
use App\Models\PlaceSubmissionEvent;
use App\Models\User;
use App\Services\PlaceSubmissionTransition;
use Illuminate\Validation\ValidationException;

final readonly class MergePlaceDuplicate
{
    public function __construct(private PlaceSubmissionTransition $transition) {}

    public function handle(
        User $actor,
        PlaceSubmission $submission,
        Place $source,
        PlaceDuplicateCandidate $candidate,
        string $operationKey,
        int $expectedLockVersion,
        string $reasonCode,
    ): PlaceSubmission {
        $destinationId = $candidate->candidate_place_id;

        if ($candidate->place_submission_id !== $submission->id
            || $destinationId === null
            || $source->id === $destinationId
            || $submission->published_place_id !== $source->id) {
            throw ValidationException::withMessages(['candidate' => __('places.submissions.validation.candidate')]);
        }

        return $this->transition->handle(
            $actor,
            $submission,
            'merge',
            [PlaceSubmissionStatus::DuplicateReview, PlaceSubmissionStatus::Published],
            PlaceSubmissionStatus::Published,
            PlaceSubmissionAction::PlacesMerged,
            $operationKey,
            $expectedLockVersion,
            $reasonCode,
            candidate: $candidate,
            destinationPlaceId: $destinationId,
            resolution: PlaceSubmissionResolution::DuplicateMerge,
            mutate: function (PlaceSubmission $locked) use ($actor, $source, $destinationId): void {
                $places = Place::query()
                    ->whereKey([$source->id, $destinationId])
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');
                $lockedSource = $places->get($source->id);
                $destination = $places->get($destinationId);

                if (! $lockedSource instanceof Place
                    || ! $destination instanceof Place
                    || $lockedSource->status !== PlaceStatus::Active
                    || $destination->status !== PlaceStatus::Active
                    || $destination->merged_into_place_id !== null) {
                    throw ValidationException::withMessages(['candidate' => __('places.submissions.validation.candidate')]);
                }

                $lockedSource->status = PlaceStatus::Merged;
                $lockedSource->merged_into_place_id = $destination->id;
                $lockedSource->lock_version++;
                $lockedSource->save();

                $this->copyFacts($lockedSource, $destination, $actor);

                foreach (array_unique([$lockedSource->stable_key, $lockedSource->slug]) as $identifier) {
                    PlaceMergeRedirect::query()->create([
                        'source_place_id' => $lockedSource->id,
                        'destination_place_id' => $destination->id,
                        'created_by_user_id' => $actor->id,
                        'source_identifier' => $identifier,
                        'source_visibility' => $lockedSource->visibility,
                        'created_at' => now(),
                    ]);
                }

                $locked->published_place_id = $lockedSource->id;
                $locked->linked_place_id = $destination->id;
            },
            afterEvent: static function (PlaceSubmission $locked, PlaceSubmissionEvent $event): void {
                PlaceMergeRedirect::query()
                    ->where('source_place_id', $locked->published_place_id)
                    ->where('destination_place_id', $locked->linked_place_id)
                    ->whereNull('place_submission_event_id')
                    ->update(['place_submission_event_id' => $event->id]);
            },
        );
    }

    private function copyFacts(Place $source, Place $destination, User $reviewer): void
    {
        $source->facts()->orderBy('id')->each(function (PlaceFact $fact) use ($source, $destination, $reviewer): void {
            PlaceFact::query()->firstOrCreate(
                ['place_id' => $destination->id, 'copied_from_fact_id' => $fact->id],
                [
                    'place_submission_id' => $fact->place_submission_id,
                    'place_submission_revision_id' => $fact->place_submission_revision_id,
                    'origin_place_id' => $source->id,
                    'submitted_by_user_id' => $fact->submitted_by_user_id,
                    'reviewed_by_user_id' => $reviewer->id,
                    'stable_key' => $fact->stable_key.'-merged-'.$destination->id,
                    'field_key' => $fact->field_key,
                    'field_value' => $fact->field_value,
                    'value_hash' => $fact->value_hash,
                    'source_kind' => $fact->source_kind,
                    'source_reference' => $fact->source_reference,
                    'provenance_scope' => PlaceFactScope::Merged,
                    'visibility_scope' => $fact->visibility_scope,
                    'observed_at' => $fact->observed_at,
                    'verified_at' => $fact->verified_at,
                    'created_at' => now(),
                ],
            );
        });
    }
}
