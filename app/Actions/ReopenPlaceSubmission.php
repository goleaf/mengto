<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\PlaceSubmissionAction;
use App\Enums\PlaceSubmissionResolution;
use App\Enums\PlaceSubmissionStatus;
use App\Models\PlaceSubmission;
use App\Models\User;
use App\Services\PlaceSubmissionTransition;

final readonly class ReopenPlaceSubmission
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
            'reopen',
            [PlaceSubmissionStatus::Rejected, PlaceSubmissionStatus::Withdrawn],
            PlaceSubmissionStatus::Submitted,
            PlaceSubmissionAction::Reopened,
            $operationKey,
            $expectedLockVersion,
            $reasonCode,
            resolution: PlaceSubmissionResolution::None,
            mutate: static function (PlaceSubmission $locked): void {
                $locked->published_place_id = null;
                $locked->linked_place_id = null;
            },
        );
    }
}
