<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\PlaceSubmissionAction;
use App\Enums\PlaceSubmissionStatus;
use App\Models\PlaceSubmission;
use App\Models\User;
use App\Services\PlaceSubmissionTransition;

final readonly class ApprovePlaceSubmission
{
    public function __construct(private PlaceSubmissionTransition $transition) {}

    public function handle(
        User $actor,
        PlaceSubmission $submission,
        string $operationKey,
        int $expectedLockVersion,
        string $reasonCode,
    ): PlaceSubmission {
        return $this->transition->handle(
            $actor,
            $submission,
            'approveNewPlace',
            [
                PlaceSubmissionStatus::Submitted,
                PlaceSubmissionStatus::DuplicateReview,
                PlaceSubmissionStatus::NeedsInformation,
            ],
            PlaceSubmissionStatus::Approved,
            PlaceSubmissionAction::NewPlaceApproved,
            $operationKey,
            $expectedLockVersion,
            $reasonCode,
        );
    }
}
