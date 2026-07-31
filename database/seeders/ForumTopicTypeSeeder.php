<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\ForumTopicType;
use App\Models\ForumTopicType as ForumTopicTypeModel;
use Illuminate\Database\Seeder;

final class ForumTopicTypeSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [];
        $now = now();

        foreach (ForumTopicType::cases() as $type) {
            $rows[] = [
                'stable_key' => $type->value,
                'name_translation_key' => "forum.topic_types.{$type->value}.name",
                'description_translation_key' => "forum.topic_types.{$type->value}.description",
                'schema_version' => 1,
                'field_schema' => json_encode($this->fieldSchema($type), JSON_THROW_ON_ERROR),
                'configuration' => json_encode($this->configuration($type), JSON_THROW_ON_ERROR),
                'moderation_level' => $this->moderationLevel($type),
                'allows_accepted_answers' => in_array($type, [
                    ForumTopicType::Question,
                    ForumTopicType::Case,
                    ForumTopicType::IdentificationRequest,
                ], true),
                'allows_confirmation' => in_array($type, [
                    ForumTopicType::IdentificationRequest,
                    ForumTopicType::CorrectionRequest,
                    ForumTopicType::Sighting,
                ], true),
                'expires' => in_array($type, [
                    ForumTopicType::EmergencyAlert,
                    ForumTopicType::UrgentRequest,
                    ForumTopicType::Event,
                    ForumTopicType::Sighting,
                ], true),
                'is_system_managed' => true,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        ForumTopicTypeModel::query()->upsert(
            $rows,
            ['stable_key'],
            [
                'name_translation_key',
                'description_translation_key',
                'schema_version',
                'field_schema',
                'configuration',
                'moderation_level',
                'allows_accepted_answers',
                'allows_confirmation',
                'expires',
                'is_system_managed',
                'is_active',
                'updated_at',
            ],
        );
    }

    /** @return array<string, array<string, mixed>> */
    private function fieldSchema(ForumTopicType $type): array
    {
        $base = [
            'title' => ['type' => 'string', 'required' => true, 'max' => 180],
            'body' => ['type' => 'string', 'required' => true, 'max' => 10000],
            'category' => ['type' => 'category-key', 'required' => true],
        ];

        return match ($type) {
            ForumTopicType::Journal => [
                ...$base,
                'journal_type' => ['type' => 'forum-journal-type', 'required' => true],
                'started_on' => ['type' => 'date', 'required' => true],
            ],
            ForumTopicType::EmergencyAlert => [
                ...$base,
                'location' => ['type' => 'location-scope', 'required' => true],
                'source_url' => ['type' => 'safe-url', 'required' => true],
                'expires_at' => ['type' => 'datetime', 'required' => true],
            ],
            ForumTopicType::Event => [
                ...$base,
                'starts_at' => ['type' => 'datetime', 'required' => true],
                'ends_at' => ['type' => 'datetime', 'required' => true],
                'location' => ['type' => 'location-scope', 'required' => false],
            ],
            ForumTopicType::IdentificationRequest => [
                ...$base,
                'observation_location' => ['type' => 'location-scope', 'required' => false],
                'observed_at' => ['type' => 'datetime', 'required' => false],
                'media' => ['type' => 'safe-media', 'required' => true],
            ],
            default => $base,
        };
    }

    /** @return array<string, mixed> */
    private function configuration(ForumTopicType $type): array
    {
        return [
            'allowed_reactions' => [
                'helpful',
                'thank-you',
                'supportive',
                'empathetic',
                'insightful',
                'well-explained',
                'good-source',
                'caution',
                'needs-clarification',
            ],
            'requires_location' => in_array($type, [
                ForumTopicType::EmergencyAlert,
                ForumTopicType::LostAnimal,
                ForumTopicType::FoundAnimal,
                ForumTopicType::Sighting,
                ForumTopicType::Event,
            ], true),
            'requires_species' => in_array($type, [
                ForumTopicType::LostAnimal,
                ForumTopicType::FoundAnimal,
                ForumTopicType::Sighting,
                ForumTopicType::IdentificationRequest,
            ], true),
            'linked_domain' => match ($type) {
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
            },
        ];
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
