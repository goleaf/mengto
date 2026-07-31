<?php

declare(strict_types=1);

namespace App\Services;

final class ForumReportReasonCatalog
{
    /** @var list<string> */
    public const KEYS = [
        'spam',
        'repetitive-posting',
        'irrelevant-advertising',
        'undisclosed-sponsored-content',
        'affiliate-link-disclosure-failure',
        'scam',
        'phishing',
        'malware',
        'suspicious-external-link',
        'impersonation',
        'stolen-account',
        'fake-organization',
        'fake-professional-credentials',
        'fake-rescue-organization',
        'fake-shelter',
        'fake-breeder',
        'adoption-scam',
        'lost-animal-scam',
        'reward-scam',
        'fundraising-fraud',
        'marketplace-fraud',
        'non-delivery',
        'counterfeit-product',
        'prohibited-product',
        'prohibited-animal-sale',
        'illegal-wildlife-trade',
        'suspected-poaching',
        'animal-cruelty',
        'animal-neglect',
        'glorification-of-cruelty',
        'dangerous-handling',
        'dangerous-training-advice',
        'dangerous-medical-advice',
        'medication-misuse',
        'false-emergency-claim',
        'severe-health-misinformation',
        'legal-misinformation',
        'public-health-misinformation',
        'product-safety-misinformation',
        'harassment',
        'targeted-harassment',
        'bullying',
        'threats',
        'stalking',
        'hate-speech',
        'discriminatory-content',
        'sexual-harassment',
        'unwanted-contact',
        'doxxing',
        'private-address-exposure',
        'private-phone-exposure',
        'private-email-exposure',
        'identity-document-exposure',
        'private-medical-information',
        'child-safety',
        'graphic-injury-without-warning',
        'graphic-death-without-warning',
        'cruelty-imagery',
        'copyright-infringement',
        'stolen-image',
        'plagiarism',
        'misinformation',
        'manipulated-evidence',
        'fabricated-review',
        'review-bombing',
        'coordinated-voting',
        'karma-manipulation',
        'fake-confirmation',
        'conflict-of-interest',
        'moderator-conflict',
        'abuse-of-authority',
        'wrong-category',
        'duplicate-topic',
        'misleading-title',
        'missing-content-warning',
        'translation-abuse',
        'deliberate-language-disruption',
        'off-topic-content',
        'prohibited-personal-transaction',
        'suspicious-animal-sourcing',
        'unsafe-rehoming',
        'irresponsible-breeding-advertisement',
        'false-lost-animal-sighting',
        'false-animal-identification',
        'dangerous-location-information',
        'outdated-critical-information',
        'violation-of-community-rules',
        'other',
    ];

    /** @var array<string, string> */
    public const LEGACY_ALIASES = [
        'duplicate' => 'duplicate-topic',
        'dangerous-advice' => 'dangerous-medical-advice',
        'fraud' => 'scam',
        'personal-data' => 'doxxing',
        'hidden-advertising' => 'undisclosed-sponsored-content',
        'illegal-sale' => 'prohibited-animal-sale',
        'copyright' => 'copyright-infringement',
        'false-report' => 'false-lost-animal-sighting',
        'stolen-photos' => 'stolen-image',
        'outdated' => 'outdated-critical-information',
        'animal-danger' => 'animal-cruelty',
        'cruelty' => 'animal-cruelty',
        'illegal-animal' => 'prohibited-animal-sale',
        'threat' => 'threats',
        'hidden-sale' => 'prohibited-animal-sale',
    ];

    /** @var list<string> */
    private const CRITICAL = [
        'phishing',
        'malware',
        'stolen-account',
        'adoption-scam',
        'lost-animal-scam',
        'fundraising-fraud',
        'marketplace-fraud',
        'illegal-wildlife-trade',
        'suspected-poaching',
        'animal-cruelty',
        'threats',
        'stalking',
        'doxxing',
        'private-address-exposure',
        'identity-document-exposure',
        'child-safety',
        'dangerous-medical-advice',
        'severe-health-misinformation',
        'false-emergency-claim',
        'reward-scam',
    ];

    /** @var list<string> */
    private const SPECIALIST = [
        'fake-professional-credentials',
        'illegal-wildlife-trade',
        'suspected-poaching',
        'animal-cruelty',
        'child-safety',
        'severe-health-misinformation',
        'legal-misinformation',
        'public-health-misinformation',
        'copyright-infringement',
        'abuse-of-authority',
    ];

    public function canonicalKey(string $key): string
    {
        return self::LEGACY_ALIASES[$key] ?? $key;
    }

    /** @return list<string> */
    public function acceptedInputKeys(): array
    {
        return [...self::KEYS, ...array_keys(self::LEGACY_ALIASES)];
    }

    public function defaultPriority(string $key): string
    {
        return in_array($this->canonicalKey($key), self::CRITICAL, true)
            ? 'critical'
            : 'standard';
    }

    public function requiresSpecialistReview(string $key): bool
    {
        return in_array($this->canonicalKey($key), self::SPECIALIST, true);
    }
}
