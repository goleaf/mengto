<?php

declare(strict_types=1);

$dimensions = [
    'helpfulness' => 'Helpfulness',
    'answer-quality' => 'Answer quality',
    'reliability' => 'Reliability',
    'evidence-quality' => 'Evidence quality',
    'empathy' => 'Empathy',
    'respectful-communication' => 'Respectful communication',
    'community-support' => 'Community support',
    'species-experience' => 'Species-specific experience',
    'category-expertise' => 'Category-specific expertise',
    'local-knowledge' => 'Local knowledge',
    'rescue-contribution' => 'Rescue contribution',
    'lost-found-contribution' => 'Lost-and-found contribution',
    'adoption-support' => 'Adoption support',
    'mentoring' => 'Mentoring',
    'guide-contribution' => 'Guide contribution',
    'correction-contribution' => 'Correction contribution',
    'moderation-contribution' => 'Moderation contribution',
    'marketplace-trust' => 'Marketplace trust',
    'service-review-reliability' => 'Service-review reliability',
    'event-reliability' => 'Event reliability',
    'professional-contribution' => 'Professional contribution',
];
$trustLevels = [
    'new-member' => 'New member',
    'member' => 'Member',
    'established-member' => 'Established member',
    'trusted-contributor' => 'Trusted contributor',
    'mentor' => 'Mentor',
    'community-reviewer' => 'Community reviewer',
    'category-steward' => 'Category steward',
    'moderator' => 'Moderator',
    'senior-moderator' => 'Senior moderator',
    'verified-professional' => 'Verified professional',
    'organization-representative' => 'Organization representative',
    'administrator' => 'Administrator',
];
$badges = [
    'onboarding' => 'Onboarding complete',
    'helpful-contributor' => 'Helpful contributor',
    'detailed-answer' => 'Detailed answer',
    'evidence-contributor' => 'Evidence contributor',
    'guide-author' => 'Guide author',
    'guide-reviewer' => 'Guide reviewer',
    'translator' => 'Translator',
    'mentor' => 'Mentor',
    'foster-supporter' => 'Foster supporter',
    'rescue-volunteer' => 'Rescue volunteer',
    'lost-animal-search-supporter' => 'Lost-animal search supporter',
    'successful-reunion-contributor' => 'Successful reunion contributor',
    'adoption-supporter' => 'Adoption supporter',
    'senior-animal-supporter' => 'Senior-animal supporter',
    'special-needs-supporter' => 'Special-needs supporter',
    'local-guide' => 'Local guide',
    'event-organizer' => 'Event organizer',
    'accessibility-contributor' => 'Accessibility contributor',
    'community-reviewer' => 'Community reviewer',
    'category-steward' => 'Category steward',
    'marketplace-reliability' => 'Marketplace reliability',
];

return [
    'dimensions' => array_map(
        static fn (string $name): array => [
            'name' => $name,
            'description' => "Verified {$name} contributions, scoped independently from other expertise.",
        ],
        $dimensions,
    ),
    'trust_levels' => array_map(
        static fn (string $name): array => [
            'name' => $name,
            'description' => "{$name} is reviewed separately from karma and professional credentials.",
        ],
        $trustLevels,
    ),
    'badges' => array_map(
        static fn (string $name): array => [
            'name' => $name,
            'description' => "{$name} recognizes verified contribution criteria and may be revoked after confirmed abuse.",
        ],
        $badges,
    ),
    'events' => [
        'helpful_vote' => 'Another member marked an answer as helpful.',
        'answer_accepted' => 'A topic author accepted the answer.',
        'reversal' => 'A previous reputation event was reversed with an audit record.',
    ],
    'messages' => [
        'self_award_forbidden' => 'You cannot award reputation to yourself.',
        'self_vote_forbidden' => 'You cannot rate your own answer.',
        'self_accept_forbidden' => 'You cannot accept your own answer.',
        'relationship_limit_reached' => 'This relationship has reached its current reputation-effect limit.',
        'invalid_confirmation_risk' => 'The requested confirmation risk class is not supported.',
        'invalid_confirmation_quorum' => 'The confirmation quorum must be between 2 and 50 reviewers.',
        'invalid_confirmation_diversity' => 'The confirmation diversity requirement must be valid for the selected quorum.',
        'invalid_confirmation_stance' => 'Choose support, oppose, or abstain.',
        'confirmation_conflict_required' => 'Describe the conflict of interest before submitting this review.',
        'confirmation_closed' => 'This confirmation is closed or has expired.',
        'duplicate_confirmation_vote' => 'You have already reviewed this confirmation.',
    ],
];
