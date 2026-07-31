<?php

declare(strict_types=1);

namespace App\Enums;

enum ForumCommunityNoteType: string
{
    case OutdatedInformation = 'outdated-information';
    case MissingContext = 'missing-context';
    case JurisdictionDifference = 'jurisdiction-difference';
    case SpeciesDifference = 'species-difference';
    case SafetyWarning = 'safety-warning';
    case SourceCorrection = 'source-correction';
    case TranslationCorrection = 'translation-correction';
    case ConflictOfInterest = 'conflict-of-interest';
    case SponsoredDisclosure = 'sponsored-disclosure';
    case ProductRecall = 'product-recall';
    case DuplicateCase = 'duplicate-case';

    public function isSafetySensitive(): bool
    {
        return in_array($this, [
            self::SafetyWarning,
            self::ProductRecall,
        ], true);
    }

    public function label(): string
    {
        return __("forum_review.note_types.{$this->value}");
    }
}
