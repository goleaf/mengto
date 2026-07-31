<?php

declare(strict_types=1);

namespace App\Enums;

enum KnowledgeStatus: string
{
    case Draft = 'draft';
    case SubmittedForReview = 'submitted-for-review';
    case ChangesRequested = 'changes-requested';
    case CommunityReviewed = 'community-reviewed';
    case ExpertReviewed = 'expert-reviewed';
    case Published = 'published';
    case CorrectionRequested = 'correction-requested';
    case Outdated = 'outdated';
    case Archived = 'archived';
    case Replaced = 'replaced';

    public function label(): string
    {
        return __("knowledge.status.{$this->value}");
    }

    public function isPublic(): bool
    {
        return in_array($this, [self::Published, self::Outdated], true);
    }

    /** @return list<self> */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Draft => [self::SubmittedForReview, self::Archived],
            self::SubmittedForReview => [
                self::ChangesRequested,
                self::CommunityReviewed,
                self::ExpertReviewed,
            ],
            self::ChangesRequested => [self::SubmittedForReview, self::Archived],
            self::CommunityReviewed => [
                self::ChangesRequested,
                self::ExpertReviewed,
                self::Published,
            ],
            self::ExpertReviewed => [self::ChangesRequested, self::Published],
            self::Published => [
                self::CorrectionRequested,
                self::Outdated,
                self::Archived,
                self::Replaced,
            ],
            self::CorrectionRequested => [self::SubmittedForReview, self::Archived],
            self::Outdated => [
                self::SubmittedForReview,
                self::Archived,
                self::Replaced,
            ],
            self::Archived => [self::Draft],
            self::Replaced => [self::Archived],
        };
    }

    /** @return list<string> */
    public static function publicValues(): array
    {
        return [
            self::Published->value,
            self::Outdated->value,
        ];
    }
}
