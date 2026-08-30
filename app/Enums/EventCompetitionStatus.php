<?php

declare(strict_types=1);

namespace App\Enums;

enum EventCompetitionStatus: string
{
    case Draft = 'draft'; case JudgingOpen = 'judging_open'; case Finalized = 'finalized'; case Published = 'published'; case Cancelled = 'cancelled';
}
