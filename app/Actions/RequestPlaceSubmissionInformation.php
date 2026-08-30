<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\PlaceSubmissionAction;
use App\Enums\PlaceSubmissionStatus;
use App\Models\PlaceDuplicateCandidate;
use App\Models\PlaceSubmission;
use App\Models\User;
use App\Services\PlaceSubmissionTransition;
use Illuminate\Support\Facades\Validator;

final readonly class RequestPlaceSubmissionInformation
{
    public function __construct(private PlaceSubmissionTransition $transition) {}

    public function handle(
        User $actor,
        PlaceSubmission $submission,
        string $operationKey,
        int $expectedLockVersion,
        string $reasonCode,
        string $reasonDetail,
        ?PlaceDuplicateCandidate $candidate = null,
    ): PlaceSubmission {
        $validated = Validator::make(
            ['reason_detail' => $reasonDetail],
            ['reason_detail' => ['required', 'string', 'min:10', 'max:2000']],
        )->validate();

        return $this->transition->handle(
            $actor,
            $submission,
            'requestInformation',
            [
                PlaceSubmissionStatus::Submitted,
                PlaceSubmissionStatus::DuplicateReview,
                PlaceSubmissionStatus::Approved,
            ],
            PlaceSubmissionStatus::NeedsInformation,
            PlaceSubmissionAction::InformationRequested,
            $operationKey,
            $expectedLockVersion,
            $reasonCode,
            (string) $validated['reason_detail'],
            $candidate,
        );
    }
}
