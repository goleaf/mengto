<?php

declare(strict_types=1);

namespace App\Data;

use App\Enums\ForumEventFormat;
use App\Enums\ForumEventPhotoConsent;

final readonly class RegisterForForumEventData
{
    public function __construct(
        public ForumEventFormat $attendanceFormat,
        public int $guestCount,
        public ?int $petProfileId,
        public ?string $requirementsNote,
        public ForumEventPhotoConsent $photoConsent,
        public bool $requirementsAccepted,
        public string $idempotencyKey,
    ) {}
}
