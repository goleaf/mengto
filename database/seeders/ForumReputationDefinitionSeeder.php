<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\ForumBadge;
use App\Models\ForumReputationDimension;
use App\Models\ForumTrustLevel;
use Illuminate\Database\Seeder;

final class ForumReputationDefinitionSeeder extends Seeder
{
    private const DIMENSIONS = [
        'helpfulness',
        'answer-quality',
        'reliability',
        'evidence-quality',
        'empathy',
        'respectful-communication',
        'community-support',
        'species-experience',
        'category-expertise',
        'local-knowledge',
        'rescue-contribution',
        'lost-found-contribution',
        'adoption-support',
        'mentoring',
        'guide-contribution',
        'correction-contribution',
        'moderation-contribution',
        'marketplace-trust',
        'service-review-reliability',
        'event-reliability',
        'professional-contribution',
    ];

    private const TRUST_LEVELS = [
        'new-member',
        'member',
        'established-member',
        'trusted-contributor',
        'mentor',
        'community-reviewer',
        'category-steward',
        'moderator',
        'senior-moderator',
        'verified-professional',
        'organization-representative',
        'administrator',
    ];

    private const BADGES = [
        'onboarding',
        'helpful-contributor',
        'detailed-answer',
        'evidence-contributor',
        'guide-author',
        'guide-reviewer',
        'translator',
        'mentor',
        'foster-supporter',
        'rescue-volunteer',
        'lost-animal-search-supporter',
        'successful-reunion-contributor',
        'adoption-supporter',
        'senior-animal-supporter',
        'special-needs-supporter',
        'local-guide',
        'event-organizer',
        'accessibility-contributor',
        'community-reviewer',
        'category-steward',
        'marketplace-reliability',
    ];

    public function run(): void
    {
        $now = now();
        $dimensionRows = [];

        foreach (self::DIMENSIONS as $key) {
            $dimensionRows[] = [
                'stable_key' => $key,
                'name_translation_key' => "forum_reputation.dimensions.{$key}.name",
                'description_translation_key' => "forum_reputation.dimensions.{$key}.description",
                'daily_actor_recipient_cap' => 10,
                'relationship_cap' => 50,
                'is_public_by_default' => ! in_array($key, [
                    'moderation-contribution',
                    'marketplace-trust',
                ], true),
                'is_active' => true,
                'metadata' => json_encode([
                    'purchasable' => false,
                    'grants_professional_verification' => false,
                    'grants_moderation_permissions' => false,
                ], JSON_THROW_ON_ERROR),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        ForumReputationDimension::query()->upsert(
            $dimensionRows,
            ['stable_key'],
            [
                'name_translation_key',
                'description_translation_key',
                'daily_actor_recipient_cap',
                'relationship_cap',
                'is_public_by_default',
                'is_active',
                'metadata',
                'updated_at',
            ],
        );
        $trustRows = [];

        foreach (self::TRUST_LEVELS as $position => $key) {
            $trustRows[] = [
                'stable_key' => $key,
                'name_translation_key' => "forum_reputation.trust_levels.{$key}.name",
                'description_translation_key' => "forum_reputation.trust_levels.{$key}.description",
                'position' => $position + 1,
                'is_professional' => $key === 'verified-professional',
                'is_moderation_role' => in_array($key, [
                    'moderator',
                    'senior-moderator',
                ], true),
                'is_active' => true,
                'criteria' => json_encode([], JSON_THROW_ON_ERROR),
                'metadata' => json_encode([
                    'automatically_grants_permissions' => false,
                    'requires_independent_review' => in_array($key, [
                        'moderator',
                        'senior-moderator',
                        'verified-professional',
                        'organization-representative',
                        'administrator',
                    ], true),
                ], JSON_THROW_ON_ERROR),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        ForumTrustLevel::query()->upsert(
            $trustRows,
            ['stable_key'],
            [
                'name_translation_key',
                'description_translation_key',
                'position',
                'is_professional',
                'is_moderation_role',
                'is_active',
                'criteria',
                'metadata',
                'updated_at',
            ],
        );
        $badgeRows = [];

        foreach (self::BADGES as $key) {
            $sensitive = in_array($key, [
                'mentor',
                'rescue-volunteer',
                'community-reviewer',
                'category-steward',
                'marketplace-reliability',
            ], true);
            $badgeRows[] = [
                'stable_key' => $key,
                'name_translation_key' => "forum_reputation.badges.{$key}.name",
                'description_translation_key' => "forum_reputation.badges.{$key}.description",
                'criteria_version' => 1,
                'criteria' => json_encode([
                    'requires_verified_events' => true,
                    'post_volume_alone_is_insufficient' => true,
                    'paid_path' => false,
                ], JSON_THROW_ON_ERROR),
                'revocation_rules' => json_encode([
                    'confirmed-abuse',
                    'fraudulent-contribution',
                ], JSON_THROW_ON_ERROR),
                'requires_moderation_review' => $sensitive,
                'expires' => $key === 'marketplace-reliability',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        ForumBadge::query()->upsert(
            $badgeRows,
            ['stable_key'],
            [
                'name_translation_key',
                'description_translation_key',
                'criteria_version',
                'criteria',
                'revocation_rules',
                'requires_moderation_review',
                'expires',
                'is_active',
                'updated_at',
            ],
        );
    }
}
