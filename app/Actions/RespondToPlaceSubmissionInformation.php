<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\PlaceSubmissionAction;
use App\Enums\PlaceSubmissionStatus;
use App\Models\PlaceSubmission;
use App\Models\PlaceSubmissionRevision;
use App\Models\User;
use App\Services\PlaceSubmissionTransition;
use Illuminate\Support\Facades\Validator;

final readonly class RespondToPlaceSubmissionInformation
{
    public function __construct(private PlaceSubmissionTransition $transition) {}

    public function handle(
        User $actor,
        PlaceSubmission $submission,
        string $operationKey,
        int $expectedLockVersion,
        string $response,
    ): PlaceSubmission {
        $validated = Validator::make(
            ['response_detail' => $response],
            ['response_detail' => ['required', 'string', 'min:10', 'max:2000']],
        )->validate();

        return $this->transition->handle(
            $actor,
            $submission,
            'respond',
            [PlaceSubmissionStatus::NeedsInformation],
            PlaceSubmissionStatus::Submitted,
            PlaceSubmissionAction::InformationProvided,
            $operationKey,
            $expectedLockVersion,
            'submitter-information-provided',
            (string) $validated['response_detail'],
            mutate: static function (PlaceSubmission $locked) use ($actor, $validated): void {
                $revisionNumber = (int) $locked->revisions()->max('revision_number') + 1;
                PlaceSubmissionRevision::query()->create([
                    'place_submission_id' => $locked->id,
                    'submitted_by_user_id' => $actor->id,
                    'stable_key' => $locked->stable_key.'-revision-'.$revisionNumber,
                    'revision_number' => $revisionNumber,
                    'kind' => 'information-response',
                    'summary' => (string) $validated['response_detail'],
                    'created_at' => now(),
                ]);
            },
            recordsReview: false,
            rateScope: 'place-submission-response',
            rateLimit: 20,
        );
    }
}
