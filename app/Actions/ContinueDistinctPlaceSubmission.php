<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\PlaceSubmissionAction;
use App\Enums\PlaceSubmissionStatus;
use App\Models\PlaceSubmission;
use App\Models\User;
use App\Services\PlaceSubmissionTransition;

final readonly class ContinueDistinctPlaceSubmission
{
    public function __construct(private PlaceSubmissionTransition $transition) {}

    public function handle(
        User $actor,
        PlaceSubmission $submission,
        string $operationKey,
        int $expectedLockVersion,
    ): PlaceSubmission {
        abort_unless(
            $submission->duplicateCandidates()
                ->where('presentation_scope', 'member_visible')
                ->whereNotNull('candidate_place_id')
                ->exists(),
            404,
        );

        return $this->transition->handle(
            $actor,
            $submission,
            'chooseDuplicate',
            [PlaceSubmissionStatus::DuplicateReview],
            PlaceSubmissionStatus::Submitted,
            PlaceSubmissionAction::ContinuedAsDistinct,
            $operationKey,
            $expectedLockVersion,
            'submitter-continued-distinct',
            mutate: static function (PlaceSubmission $locked): void {
                $locked->continued_as_distinct = true;
            },
            recordsReview: false,
            rateScope: 'place-submission-choice',
            rateLimit: 20,
        );
    }
}
