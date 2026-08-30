<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\PlaceSubmissionAction;
use App\Enums\PlaceSubmissionStatus;
use App\Models\PlaceDuplicateCandidate;
use App\Models\PlaceSubmission;
use App\Models\User;
use App\Services\PlaceSubmissionTransition;

final readonly class ConfirmPlaceDuplicateCandidate
{
    public function __construct(private PlaceSubmissionTransition $transition) {}

    public function handle(
        User $actor,
        PlaceSubmission $submission,
        PlaceDuplicateCandidate $candidate,
        string $operationKey,
        int $expectedLockVersion,
    ): PlaceSubmission {
        abort_unless(
            $candidate->place_submission_id === $submission->id
                && $candidate->candidate_place_id !== null
                && $candidate->presentation_scope === 'member_visible',
            404,
        );

        return $this->transition->handle(
            $actor,
            $submission,
            'chooseDuplicate',
            [PlaceSubmissionStatus::DuplicateReview],
            PlaceSubmissionStatus::DuplicateReview,
            PlaceSubmissionAction::ExistingPlaceConfirmed,
            $operationKey,
            $expectedLockVersion,
            'submitter-confirmed-existing',
            candidate: $candidate,
            destinationPlaceId: $candidate->candidate_place_id,
            recordsReview: false,
            notifySubmitter: false,
            rateScope: 'place-submission-choice',
            rateLimit: 20,
        );
    }
}
