<?php

declare(strict_types=1);

namespace App\Enums;

enum KnowledgeTranslationSource: string
{
    case HumanCommunity = 'human-community';

    public function label(): string
    {
        return __("knowledge.translation_sources.{$this->value}");
    }
}
