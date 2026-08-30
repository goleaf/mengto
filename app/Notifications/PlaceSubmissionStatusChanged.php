<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Enums\PlaceSubmissionStatus;
use App\Models\PlaceSubmission;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

final class PlaceSubmissionStatusChanged extends Notification
{
    use Queueable;

    public readonly int $submissionId;

    public readonly string $submissionKey;

    public function __construct(
        PlaceSubmission $submission,
        public readonly PlaceSubmissionStatus $status,
    ) {
        $this->submissionId = $submission->id;
        $this->submissionKey = $submission->stable_key;
    }

    /** @return list<string> */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /** @return array<string, string> */
    public function toArray(object $notifiable): array
    {
        return [
            'submission_key' => $this->submissionKey,
            'status' => $this->status->value,
            'message_key' => 'places.submissions.notifications.'.$this->status->value,
        ];
    }
}
