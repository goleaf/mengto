<?php

declare(strict_types=1);

namespace App\Data;

use App\Enums\PlaceOpeningState;
use Carbon\CarbonImmutable;

final readonly class PlaceOpeningStateResult
{
    public function __construct(
        public PlaceOpeningState $state,
        public ?bool $appointmentOnly,
        public CarbonImmutable $evaluatedAt,
        public ?CarbonImmutable $nextTransitionAt,
        public ?CarbonImmutable $freshUntil,
        public ?string $timezone,
        public string $reason,
    ) {}
}
