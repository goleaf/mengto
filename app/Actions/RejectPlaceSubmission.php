<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\PlaceSubmissionAction;
use App\Enums\PlaceSubmissionStatus;
use App\Models\PlaceSubmission;
use App\Models\User;
use App\Services\PlaceSubmissionTransition;

final readonly class RejectPlaceSubmission
{
    public function __construct(private PlaceSubmissionTransition $transition) {}

    public function handle(
        User $actor,
        PlaceSubmission $submission,
        string $operationKey,
        int $expectedLockVersion,
        string $reasonCode,
        ?string $reasonDetail,
    ): PlaceSubmission {
        return $this->transition->handle(
            $actor,
            $submission,
            'reject',
            [
                PlaceSubmissionStatus::Submitted,
                PlaceSubmissionStatus::DuplicateReview,
                PlaceSubmissionStatus::NeedsInformation,
                PlaceSubmissionStatus::Approved,
            ],
            PlaceSubmissionStatus::Rejected,
            PlaceSubmissionAction::Rejected,
            $operationKey,
            $expectedLockVersion,
            $reasonCode,
            $reasonDetail,
            mutate: static function (PlaceSubmission $locked): void {
                $locked->approved_at = null;
            },
        );
    }
}
