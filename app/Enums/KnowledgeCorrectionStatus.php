<?php

declare(strict_types=1);

namespace App\Enums;

enum KnowledgeCorrectionStatus: string
{
    case Submitted = 'submitted';
    case Accepted = 'accepted';
    case Rejected = 'rejected';
    case Applied = 'applied';
    case Withdrawn = 'withdrawn';

    public function label(): string
    {
        return __("knowledge.correction_status.{$this->value}");
    }
}
