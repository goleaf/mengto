<?php

declare(strict_types=1);

namespace App\Services;

use App\Data\ForumEventTypeDefinition;
use App\Enums\ForumEventStatus;
use App\Enums\ForumEventType;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

final readonly class ForumEventTypeRegistry
{
    /** @var array<string, string> */
    private const LEGACY_MAPPINGS = [
        'walk' => 'group_walk',
        'training' => 'training_session',
        'show' => 'exhibition',
        'adoption' => 'adoption_day',
        'volunteer' => 'volunteer_shift',
        'celebration' => 'social_meetup',
        'online_session' => 'webinar',
        'club_meetup' => 'social_meetup',
        'other' => 'custom',
    ];

    /** @var list<string> */
    private const BUILDER_SECTIONS = [
        'context',
        'basics',
        'time_recurrence',
        'format_location',
        'participants_capacity',
        'requirements',
        'safety_accessibility_privacy',
        'review_publish',
    ];

    /** @return array<string, ForumEventTypeDefinition> */
    public function creatableDefinitions(): array
    {
        $definitions = [];

        foreach ($this->canonicalTypes() as $type) {
            $definitions[$type->value] = $this->canonicalDefinition($type);
        }

        return $definitions;
    }

    /** @return array<string, string> */
    public function legacyMappings(): array
    {
        return self::LEGACY_MAPPINGS;
    }

    public function isCreatable(ForumEventType $type): bool
    {
        return ! array_key_exists($type->value, self::LEGACY_MAPPINGS);
    }

    public function definition(ForumEventType|string $type): ForumEventTypeDefinition
    {
        $eventType = is_string($type) ? ForumEventType::from($type) : $type;
        $canonicalValue = self::LEGACY_MAPPINGS[$eventType->value] ?? $eventType->value;

        return $this->canonicalDefinition(ForumEventType::from($canonicalValue));
    }

    /** @param array<string, mixed> $configuration
     * @return array<string, mixed>
     */
    public function validateConfiguration(
        ForumEventType $type,
        array $configuration,
    ): array {
        $definition = $this->definition($type);

        if (! $this->isCreatable($type)) {
            throw ValidationException::withMessages([
                'type' => __('forum_events.validation.legacy_type_read_only'),
            ]);
        }

        $allowedKeys = $definition->type === ForumEventType::Custom
            ? ['schema_version', 'activity_model']
            : ['schema_version'];
        $unknownKeys = array_values(array_diff(array_keys($configuration), $allowedKeys));

        if ($unknownKeys !== []) {
            throw ValidationException::withMessages([
                'type_configuration' => __('forum_events.validation.type_configuration_unknown'),
            ]);
        }

        $rules = [
            'schema_version' => ['required', 'integer', Rule::in([$definition->schemaVersion])],
            'activity_model' => $definition->type === ForumEventType::Custom
                ? ['required', 'string', Rule::in([
                    'community',
                    'education',
                    'care',
                    'fundraising',
                    'organization',
                ])]
                : ['prohibited'],
        ];

        $validated = Validator::make($configuration, $rules)->validate();

        return Arr::only($validated, $allowedKeys);
    }

    /** @return list<ForumEventType> */
    private function canonicalTypes(): array
    {
        return [
            ForumEventType::SocialMeetup,
            ForumEventType::GroupWalk,
            ForumEventType::TrainingSession,
            ForumEventType::Workshop,
            ForumEventType::Conference,
            ForumEventType::Webinar,
            ForumEventType::Exhibition,
            ForumEventType::Competition,
            ForumEventType::AdoptionDay,
            ForumEventType::ShelterOpenDay,
            ForumEventType::Fundraiser,
            ForumEventType::VolunteerShift,
            ForumEventType::OrganizationMeeting,
            ForumEventType::MarketplaceFair,
            ForumEventType::ControlledAnimalIntroduction,
            ForumEventType::Custom,
        ];
    }

    private function canonicalDefinition(ForumEventType $type): ForumEventTypeDefinition
    {
        $capabilities = match ($type) {
            ForumEventType::GroupWalk => ['recurrence', 'directory', 'routes'],
            ForumEventType::TrainingSession, ForumEventType::Workshop => ['recurrence', 'sessions', 'directory'],
            ForumEventType::Conference => ['recurrence', 'sessions', 'ticketing', 'online', 'directory'],
            ForumEventType::Webinar => ['recurrence', 'sessions', 'online', 'directory'],
            ForumEventType::Exhibition => ['sessions', 'ticketing', 'directory'],
            ForumEventType::Competition => ['sessions', 'competition', 'ticketing', 'directory'],
            ForumEventType::AdoptionDay => ['ticketing', 'directory'],
            ForumEventType::ShelterOpenDay, ForumEventType::Fundraiser,
            ForumEventType::MarketplaceFair => ['recurrence', 'ticketing', 'directory'],
            ForumEventType::ControlledAnimalIntroduction => ['directory'],
            ForumEventType::OrganizationMeeting => ['recurrence', 'online'],
            ForumEventType::VolunteerShift => ['recurrence', 'directory'],
            ForumEventType::SocialMeetup => ['recurrence', 'online', 'directory'],
            ForumEventType::Custom => [],
            default => [],
        };
        $riskTier = match ($type) {
            ForumEventType::ControlledAnimalIntroduction => 'controlled',
            ForumEventType::GroupWalk,
            ForumEventType::TrainingSession,
            ForumEventType::Exhibition,
            ForumEventType::Competition,
            ForumEventType::AdoptionDay,
            ForumEventType::ShelterOpenDay => 'high',
            ForumEventType::Custom => 'review',
            default => 'standard',
        };
        $icon = match ($type) {
            ForumEventType::GroupWalk => 'route',
            ForumEventType::TrainingSession, ForumEventType::Workshop => 'graduation-cap',
            ForumEventType::Conference, ForumEventType::Webinar => 'presentation',
            ForumEventType::Exhibition => 'gallery-horizontal',
            ForumEventType::Competition => 'trophy',
            ForumEventType::AdoptionDay, ForumEventType::ShelterOpenDay => 'heart-handshake',
            ForumEventType::Fundraiser => 'badge-euro',
            ForumEventType::VolunteerShift => 'hand-heart',
            ForumEventType::OrganizationMeeting => 'building-2',
            ForumEventType::MarketplaceFair => 'store',
            ForumEventType::ControlledAnimalIntroduction => 'shield-check',
            ForumEventType::Custom => 'settings-2',
            default => 'users-round',
        };
        $participantModel = $type === ForumEventType::VolunteerShift
            ? 'assigned_participants'
            : 'individuals_and_households';
        $petModel = match ($type) {
            ForumEventType::Conference,
            ForumEventType::Webinar,
            ForumEventType::OrganizationMeeting,
            ForumEventType::Fundraiser => 'humans_only',
            ForumEventType::ControlledAnimalIntroduction => 'controlled_multi_pet',
            default => 'optional_multi_pet',
        };
        $organizerKinds = $type === ForumEventType::OrganizationMeeting
            ? ['organization']
            : ['individual', 'organization'];
        $sections = self::BUILDER_SECTIONS;

        if (in_array('sessions', $capabilities, true)) {
            array_splice($sections, -1, 0, ['schedule_media_commercial']);
        }

        return new ForumEventTypeDefinition(
            type: $type,
            nameTranslationKey: 'forum_events.types.'.$type->value,
            descriptionTranslationKey: 'forum_events.type_descriptions.'.$type->value,
            category: $type->category(),
            schemaVersion: 1,
            organizerKinds: $organizerKinds,
            participantModel: $participantModel,
            petModel: $petModel,
            builderSections: $sections,
            capabilities: $capabilities,
            riskTier: $riskTier,
            icon: $icon,
            defaultStatus: ForumEventStatus::Draft,
            factoryState: $this->factoryState($type),
            seedScenario: 'canonical-'.$type->value,
        );
    }

    private function factoryState(ForumEventType $type): string
    {
        return match ($type) {
            ForumEventType::SocialMeetup => 'socialMeetup',
            ForumEventType::GroupWalk => 'groupWalk',
            ForumEventType::TrainingSession => 'trainingSession',
            ForumEventType::ShelterOpenDay => 'shelterOpenDay',
            ForumEventType::AdoptionDay => 'adoptionDay',
            ForumEventType::VolunteerShift => 'volunteerShift',
            ForumEventType::OrganizationMeeting => 'organizationMeeting',
            ForumEventType::MarketplaceFair => 'marketplaceFair',
            default => lcfirst($type->name),
        };
    }
}
