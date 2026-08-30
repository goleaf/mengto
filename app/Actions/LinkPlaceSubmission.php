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
use App\Models\PlaceSubmission;
use App\Models\User;
use App\Services\PlaceSubmissionTransition;
use Illuminate\Validation\ValidationException;

final readonly class LinkPlaceSubmission
{
    public function __construct(private PlaceSubmissionTransition $transition) {}

    public function handle(
        User $actor,
        PlaceSubmission $submission,
        PlaceDuplicateCandidate $candidate,
        string $operationKey,
        int $expectedLockVersion,
        string $reasonCode,
    ): PlaceSubmission {
        if ($candidate->place_submission_id !== $submission->id || $candidate->candidate_place_id === null) {
            throw ValidationException::withMessages(['candidate' => __('places.submissions.validation.candidate')]);
        }

        $destination = Place::query()->findOrFail($candidate->candidate_place_id);

        if ($destination->status !== PlaceStatus::Active) {
            throw ValidationException::withMessages(['candidate' => __('places.submissions.validation.candidate')]);
        }

        return $this->transition->handle(
            $actor,
            $submission,
            'linkExisting',
            [PlaceSubmissionStatus::DuplicateReview, PlaceSubmissionStatus::Approved],
            PlaceSubmissionStatus::Published,
            PlaceSubmissionAction::ExistingPlaceLinked,
            $operationKey,
            $expectedLockVersion,
            $reasonCode,
            candidate: $candidate,
            destinationPlaceId: $destination->id,
            resolution: PlaceSubmissionResolution::ExistingLink,
            mutate: function (PlaceSubmission $locked) use ($actor, $destination): void {
                $locked->linked_place_id = $destination->id;
                $locked->published_place_id = null;
                $this->copyFacts($locked, $destination, $actor);
            },
        );
    }

    private function copyFacts(PlaceSubmission $submission, Place $destination, User $reviewer): void
    {
        $submission->facts()->orderBy('id')->each(function (PlaceFact $source) use ($destination, $reviewer): void {
            PlaceFact::query()->firstOrCreate(
                [
                    'place_id' => $destination->id,
                    'copied_from_fact_id' => $source->id,
                ],
                [
                    'place_submission_id' => $source->place_submission_id,
                    'place_submission_revision_id' => $source->place_submission_revision_id,
                    'submitted_by_user_id' => $source->submitted_by_user_id,
                    'reviewed_by_user_id' => $reviewer->id,
                    'stable_key' => $source->stable_key.'-linked-'.$destination->id,
                    'field_key' => $source->field_key,
                    'field_value' => $source->field_value,
                    'value_hash' => $source->value_hash,
                    'source_kind' => $source->source_kind,
                    'source_reference' => $source->source_reference,
                    'provenance_scope' => PlaceFactScope::Linked,
                    'visibility_scope' => $source->visibility_scope,
                    'observed_at' => $source->observed_at,
                    'verified_at' => now(),
                    'created_at' => now(),
                ],
            );
        });
    }
}
