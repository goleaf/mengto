<?php

declare(strict_types=1);

namespace App\Data;

final readonly class LocalOpeningInterval
{
    public function __construct(
        public int $startsAtMinute,
        public int $endsAtMinute,
        public bool $appointmentOnly = false,
    ) {}
}
