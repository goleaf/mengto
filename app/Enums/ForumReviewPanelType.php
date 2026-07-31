<?php

declare(strict_types=1);

namespace App\Enums;

enum ForumReviewPanelType: string
{
    case DuplicateTopic = 'duplicate-topic';
    case WrongCategory = 'wrong-category';
    case Tag = 'tag';
    case Translation = 'translation';
    case GuideClarity = 'guide-clarity';
    case IdentificationConfidence = 'identification-confidence';
    case ContentQuality = 'content-quality';

    public function label(): string
    {
        return __("forum_review.panel_types.{$this->value}");
    }
}
