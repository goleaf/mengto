<?php

declare(strict_types=1);

namespace App\Enums;

enum MedicalKnowledgeStatus: string
{
    case Unknown = 'unknown';
    case NotProvided = 'not-provided';
    case NoneKnown = 'none-known';
    case Known = 'known';

    public function label(): string
    {
        return __("medical.knowledge_statuses.{$this->value}");
    }
}
