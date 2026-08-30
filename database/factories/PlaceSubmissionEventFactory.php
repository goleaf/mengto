<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\PlaceSubmissionAction;
use App\Enums\PlaceSubmissionStatus;
use App\Models\PlaceSubmission;
use App\Models\PlaceSubmissionEvent;

/** @extends ApplicationFactory<PlaceSubmissionEvent> */
final class PlaceSubmissionEventFactory extends ApplicationFactory
{
    protected $model = PlaceSubmissionEvent::class;

    public function definition(): array
    {
        return [
            'place_submission_id' => PlaceSubmission::factory(),
            'action' => PlaceSubmissionAction::Submitted,
            'from_status' => null,
            'to_status' => PlaceSubmissionStatus::Submitted,
            'reason_code' => 'community-submission',
            'audit_context' => ['channel' => 'factory'],
            'expected_lock_version' => null,
            'result_lock_version' => 0,
            'created_at' => now(),
        ];
    }
}
