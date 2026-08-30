<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\PlaceSubmissionAction;
use App\Enums\PlaceSubmissionStatus;
use App\Models\PlaceSubmission;
use App\Models\User;
use App\Services\PlaceSubmissionTransition;

final readonly class WithdrawPlaceSubmission
{
    public function __construct(private PlaceSubmissionTransition $transition) {}

    public function handle(
        User $actor,
        PlaceSubmission $submission,
        string $operationKey,
        int $expectedLockVersion,
    ): PlaceSubmission {
        return $this->transition->handle(
            $actor,
            $submission,
            'withdraw',
            [
                PlaceSubmissionStatus::Submitted,
                PlaceSubmissionStatus::NeedsInformation,
                PlaceSubmissionStatus::DuplicateReview,
                PlaceSubmissionStatus::Approved,
            ],
            PlaceSubmissionStatus::Withdrawn,
            PlaceSubmissionAction::Withdrawn,
            $operationKey,
            $expectedLockVersion,
            'submitter-withdrawn',
            recordsReview: false,
            rateScope: 'place-submission-response',
            rateLimit: 20,
        );
    }
}
