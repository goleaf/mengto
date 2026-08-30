<?php

declare(strict_types=1);

namespace App\Enums;

enum PlaceWarningResolution: string
{
    case ConditionEnded = 'condition_ended';
    case Corrected = 'corrected';
    case FalseReport = 'false_report';
    case InsufficientEvidence = 'insufficient_evidence';
    case Expired = 'expired';
    case Removed = 'removed';
}
