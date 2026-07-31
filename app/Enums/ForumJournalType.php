<?php

declare(strict_types=1);

namespace App\Enums;

enum ForumJournalType: string
{
    case General = 'general';
    case Training = 'training';
    case Behavior = 'behavior';
    case Recovery = 'recovery';
    case Weight = 'weight';
    case Rehabilitation = 'rehabilitation';
    case AdoptionAdaptation = 'adoption-adaptation';
    case Foster = 'foster';
    case Aquarium = 'aquarium';
    case Terrarium = 'terrarium';
    case PregnancyNewborn = 'pregnancy-newborn';
    case SeniorCare = 'senior-care';

    public function label(): string
    {
        return __("forum_journals.types.{$this->value}");
    }
}
