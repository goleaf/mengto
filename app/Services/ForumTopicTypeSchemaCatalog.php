<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\ForumSubscriptionLevel;
use App\Enums\ForumTopicType;
use App\Enums\ForumVoteValue;

final readonly class ForumTopicTypeSchemaCatalog
{
    /**
     * @return list<array{
     *     stable_key: string,
     *     name_translation_key: string,
     *     description_translation_key: string,
     *     schema_version: int,
     *     field_schema: array<string, array{type: string, required: bool, validation: list<string>}>,
     *     configuration: array<string, mixed>,
     *     moderation_level: string,
     *     allows_accepted_answers: bool,
     *     allows_confirmation: bool,
     *     expires: bool,
     *     is_system_managed: bool,
     *     is_active: bool
     * }>
     */
    public function definitions(): array
    {
        return array_map(
            fn (ForumTopicType $type): array => $this->definition($type),
            ForumTopicType::cases(),
        );
    }

    /**
     * @return array{
     *     stable_key: string,
     *     name_translation_key: string,
     *     description_translation_key: string,
     *     schema_version: int,
     *     field_schema: array<string, array{type: string, required: bool, validation: list<string>}>,
     *     configuration: array<string, mixed>,
     *     moderation_level: string,
     *     allows_accepted_answers: bool,
     *     allows_confirmation: bool,
     *     expires: bool,
     *     is_system_managed: bool,
     *     is_active: bool
     * }
     */
    private function definition(ForumTopicType $type): array
    {
        $acceptsAnswers = in_array($type, [
            ForumTopicType::Question,
            ForumTopicType::Case,
            ForumTopicType::IdentificationRequest,
        ], true);
        $allowsConfirmation = in_array($type, [
            ForumTopicType::IdentificationRequest,
            ForumTopicType::CorrectionRequest,
            ForumTopicType::Sighting,
        ], true);
        $expires = in_array($type, [
            ForumTopicType::EmergencyAlert,
            ForumTopicType::UrgentRequest,
            ForumTopicType::Event,
            ForumTopicType::Sighting,
        ], true);

        return [
            'stable_key' => $type->value,
            'name_translation_key' => "forum.topic_types.{$type->value}.name",
            'description_translation_key' => "forum.topic_types.{$type->value}.description",
            'schema_version' => 1,
            'field_schema' => $this->fieldSchema($type),
            'configuration' => $this->configuration(
                $type,
                $acceptsAnswers,
                $expires,
            ),
            'moderation_level' => $this->moderationLevel($type),
            'allows_accepted_answers' => $acceptsAnswers,
            'allows_confirmation' => $allowsConfirmation,
            'expires' => $expires,
            'is_system_managed' => true,
            'is_active' => true,
        ];
    }

    /**
     * @return array<string, array{type: string, required: bool, validation: list<string>}>
     */
    private function fieldSchema(ForumTopicType $type): array
    {
        $base = [
            'title' => [
                'type' => 'string',
                'required' => true,
                'validation' => ['string', 'min:20', 'max:180'],
            ],
            'body' => [
                'type' => 'string',
                'required' => true,
                'validation' => ['string', 'min:60', 'max:10000'],
            ],
            'category' => [
                'type' => 'category-key',
                'required' => true,
                'validation' => ['string', 'max:120'],
            ],
            'location' => [
                'type' => 'location-scope',
                'required' => false,
                'validation' => ['string', 'max:120'],
            ],
            'taxon_ids' => [
                'type' => 'taxon-id-list',
                'required' => false,
                'validation' => ['array', 'max:5'],
            ],
        ];

        return match ($type) {
            ForumTopicType::Journal => [
                ...$base,
                'journal_type' => [
                    'type' => 'forum-journal-type',
                    'required' => true,
                    'validation' => ['string', 'max:80'],
                ],
                'started_on' => [
                    'type' => 'date',
                    'required' => true,
                    'validation' => ['date'],
                ],
            ],
            ForumTopicType::EmergencyAlert => [
                ...$base,
                'location' => [
                    'type' => 'location-scope',
                    'required' => true,
                    'validation' => ['string', 'max:120'],
                ],
                'source_url' => [
                    'type' => 'safe-url',
                    'required' => true,
                    'validation' => ['url', 'max:2048'],
                ],
                'expires_at' => [
                    'type' => 'datetime',
                    'required' => true,
                    'validation' => ['date', 'after:now'],
                ],
            ],
            ForumTopicType::Event => [
                ...$base,
                'starts_at' => [
                    'type' => 'datetime',
                    'required' => true,
                    'validation' => ['date'],
                ],
                'ends_at' => [
                    'type' => 'datetime',
                    'required' => true,
                    'validation' => ['date', 'after:starts_at'],
                ],
            ],
            ForumTopicType::IdentificationRequest => [
                ...$base,
                'observation_location' => [
                    'type' => 'location-scope',
                    'required' => false,
                    'validation' => ['string', 'max:120'],
                ],
                'observed_at' => [
                    'type' => 'datetime',
                    'required' => false,
                    'validation' => ['date', 'before_or_equal:now'],
                ],
                'media' => [
                    'type' => 'safe-media',
                    'required' => true,
                    'validation' => ['array', 'min:1', 'max:5'],
                ],
            ],
            default => $base,
        };
    }

    /** @return array<string, mixed> */
    private function configuration(
        ForumTopicType $type,
        bool $acceptsAnswers,
        bool $expires,
    ): array {
        $requiresLocation = in_array($type, [
            ForumTopicType::EmergencyAlert,
            ForumTopicType::LostAnimal,
            ForumTopicType::FoundAnimal,
            ForumTopicType::Sighting,
            ForumTopicType::Event,
        ], true);
        $requiresSpecies = in_array($type, [
            ForumTopicType::LostAnimal,
            ForumTopicType::FoundAnimal,
            ForumTopicType::Sighting,
            ForumTopicType::IdentificationRequest,
        ], true);

        return [
            'expiration' => [
                'enabled' => $expires,
                'default_days' => $expires ? 30 : null,
                'action' => $expires ? 'review' : 'none',
            ],
            'archival' => [
                'enabled' => true,
                'inactive_days' => $expires ? 90 : 365,
                'action' => 'archive',
            ],
            'requires_location' => $requiresLocation,
            'requires_species' => $requiresSpecies,
            'contact_restriction' => [
                'mode' => $requiresLocation ? 'platform-relay' : 'public-safe',
                'allow_direct_contact_fields' => false,
            ],
            'allowed_attachments' => ['image', 'video'],
            'allowed_reactions' => ForumVoteValue::values(),
            'accepted_answers' => [
                'enabled' => $acceptsAnswers,
                'multiple' => false,
            ],
            'seo' => [
                'indexable' => ! in_array($type, [
                    ForumTopicType::EmergencyAlert,
                    ForumTopicType::SupportRequest,
                ], true),
                'canonical' => 'topic',
            ],
            'notifications' => [
                'levels' => array_column(ForumSubscriptionLevel::cases(), 'value'),
                'default' => ForumSubscriptionLevel::All->value,
            ],
            'linked_domain' => $this->linkedDomain($type),
        ];
    }

    private function linkedDomain(ForumTopicType $type): ?string
    {
        return match ($type) {
            ForumTopicType::LostAnimal,
            ForumTopicType::FoundAnimal,
            ForumTopicType::Sighting,
            ForumTopicType::LostPet => 'search-case',
            ForumTopicType::AdoptionListing,
            ForumTopicType::FosterRequest => 'adoption',
            ForumTopicType::MarketplaceListing => 'marketplace',
            ForumTopicType::Event => 'event',
            ForumTopicType::Journal => 'forum-journal',
            default => null,
        };
    }

    private function moderationLevel(ForumTopicType $type): string
    {
        return match ($type) {
            ForumTopicType::EmergencyAlert,
            ForumTopicType::LostAnimal,
            ForumTopicType::FoundAnimal,
            ForumTopicType::AdoptionListing,
            ForumTopicType::MarketplaceListing => 'elevated',
            default => 'standard',
        };
    }
}
