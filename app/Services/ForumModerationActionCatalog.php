<?php

declare(strict_types=1);

namespace App\Services;

final class ForumModerationActionCatalog
{
    /** @var list<string> */
    public const KEYS = [
        'no-action',
        'educational-notice',
        'request-clarification',
        'content-warning',
        'edit-request',
        'moderator-redaction',
        'sensitive-data-removal',
        'content-relocation',
        'duplicate-merge',
        'content-lock',
        'temporary-content-hiding',
        'content-removal',
        'reaction-removal',
        'reputation-reversal',
        'confirmation-cancellation',
        'badge-revocation',
        'warning',
        'temporary-posting-limit',
        'temporary-reply-limit',
        'upload-restriction',
        'private-message-restriction',
        'marketplace-restriction',
        'category-specific-restriction',
        'local-community-restriction',
        'temporary-suspension',
        'permanent-suspension',
        'organization-restriction',
        'professional-badge-suspension',
        'emergency-account-protection',
        'forced-password-reset',
        'legal-safety-referral',
    ];

    /** @var list<string> */
    private const RESTRICTIVE = [
        'content-lock',
        'temporary-content-hiding',
        'content-removal',
        'reaction-removal',
        'reputation-reversal',
        'confirmation-cancellation',
        'badge-revocation',
        'temporary-posting-limit',
        'temporary-reply-limit',
        'upload-restriction',
        'private-message-restriction',
        'marketplace-restriction',
        'category-specific-restriction',
        'local-community-restriction',
        'temporary-suspension',
        'permanent-suspension',
        'organization-restriction',
        'professional-badge-suspension',
        'emergency-account-protection',
        'forced-password-reset',
    ];

    /** @var list<string> */
    private const REQUIRES_END = [
        'temporary-content-hiding',
        'temporary-posting-limit',
        'temporary-reply-limit',
        'upload-restriction',
        'private-message-restriction',
        'marketplace-restriction',
        'category-specific-restriction',
        'local-community-restriction',
        'temporary-suspension',
    ];

    /** @var list<string> */
    private const SENIOR_REVIEW = [
        'permanent-suspension',
        'legal-safety-referral',
    ];

    public function isRestrictive(string $key): bool
    {
        return in_array($key, self::RESTRICTIVE, true);
    }

    public function requiresEnd(string $key): bool
    {
        return in_array($key, self::REQUIRES_END, true);
    }

    public function requiresSeniorReview(string $key): bool
    {
        return in_array($key, self::SENIOR_REVIEW, true);
    }
}
