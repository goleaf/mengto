<?php

declare(strict_types=1);

use App\Enums\ForumEventType;
use App\Services\ForumEventTypeRegistry;
use Illuminate\Validation\ValidationException;

test('registry exposes sixteen creatable definitions and explicit read only legacy mappings', function () {
    $registry = app(ForumEventTypeRegistry::class);

    expect(array_keys($registry->creatableDefinitions()))
        ->toEqualCanonicalizing([
            'social_meetup',
            'group_walk',
            'training_session',
            'workshop',
            'conference',
            'webinar',
            'exhibition',
            'competition',
            'adoption_day',
            'shelter_open_day',
            'fundraiser',
            'volunteer_shift',
            'organization_meeting',
            'marketplace_fair',
            'controlled_animal_introduction',
            'custom',
        ])
        ->and($registry->legacyMappings())->toBe([
            'walk' => 'group_walk',
            'training' => 'training_session',
            'show' => 'exhibition',
            'adoption' => 'adoption_day',
            'volunteer' => 'volunteer_shift',
            'celebration' => 'social_meetup',
            'online_session' => 'webinar',
            'club_meetup' => 'social_meetup',
            'other' => 'custom',
        ])
        ->and($registry->isCreatable(ForumEventType::Walk))->toBeFalse()
        ->and($registry->definition(ForumEventType::Walk)->type)
        ->toBe(ForumEventType::GroupWalk);
});

test('every creatable event type has complete localized metadata', function () {
    $registry = app(ForumEventTypeRegistry::class);

    foreach ($registry->creatableDefinitions() as $definition) {
        expect($definition->schemaVersion)->toBe(1)
            ->and($definition->category)->not->toBe('')
            ->and($definition->organizerKinds)->not->toBeEmpty()
            ->and($definition->participantModel)->not->toBe('')
            ->and($definition->petModel)->not->toBe('')
            ->and($definition->builderSections)->toContain('review_publish')
            ->and($definition->icon)->toMatch('/^[a-z0-9-]+$/')
            ->and($definition->factoryState)->not->toBe('')
            ->and($definition->seedScenario)->not->toBe('');

        foreach (['en', 'lt', 'ru'] as $locale) {
            expect(trans($definition->nameTranslationKey, locale: $locale))
                ->not->toBe($definition->nameTranslationKey)
                ->and(trans($definition->descriptionTranslationKey, locale: $locale))
                ->not->toBe($definition->descriptionTranslationKey);
        }
    }
});

test('type configuration rejects unknown unsafe and mismatched values', function () {
    $registry = app(ForumEventTypeRegistry::class);

    expect(fn () => $registry->validateConfiguration(
        ForumEventType::Custom,
        ['schema_version' => 1, 'activity_model' => 'community', 'unknown' => true],
    ))->toThrow(ValidationException::class)
        ->and(fn () => $registry->validateConfiguration(
            ForumEventType::Custom,
            ['schema_version' => 2, 'activity_model' => 'community'],
        ))->toThrow(ValidationException::class)
        ->and(fn () => $registry->validateConfiguration(
            ForumEventType::Custom,
            ['schema_version' => 1, 'activity_model' => 'live_animal_sale'],
        ))->toThrow(ValidationException::class)
        ->and(fn () => $registry->validateConfiguration(
            ForumEventType::Custom,
            ['schema_version' => 1, 'activity_model' => 'wagering'],
        ))->toThrow(ValidationException::class)
        ->and($registry->validateConfiguration(
            ForumEventType::Custom,
            ['schema_version' => 1, 'activity_model' => 'community'],
        ))->toBe([
            'schema_version' => 1,
            'activity_model' => 'community',
        ]);
});
