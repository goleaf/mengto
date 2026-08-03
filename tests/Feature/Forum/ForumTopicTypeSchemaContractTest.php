<?php

declare(strict_types=1);

use App\Enums\ForumTopicType;
use App\Models\ForumTopic;
use App\Models\ForumTopicType as ForumTopicTypeModel;
use Database\Seeders\ForumTopicTypeSeeder;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Schema;

const SOURCE_TOPIC_TYPE_KEYS = [
    'discussion',
    'question',
    'case',
    'journal',
    'guide',
    'urgent-request',
    'emergency-alert',
    'lost-animal',
    'found-animal',
    'sighting',
    'adoption-listing',
    'foster-request',
    'volunteer-request',
    'service-review',
    'product-review',
    'place-review',
    'event',
    'poll',
    'comparison',
    'checklist',
    'marketplace-listing',
    'support-request',
    'correction-request',
    'identification-request',
    'research-discussion',
    'organization-announcement',
];

test('source topic types have stable active versioned definitions in every locale', function () {
    $this->seed(ForumTopicTypeSeeder::class);

    $enumKeys = collect(ForumTopicType::cases())
        ->map(static fn (ForumTopicType $type): string => $type->value);
    $definitions = ForumTopicTypeModel::query()
        ->whereIn('stable_key', SOURCE_TOPIC_TYPE_KEYS)
        ->orderBy('stable_key')
        ->get([
            'id',
            'stable_key',
            'name_translation_key',
            'description_translation_key',
            'schema_version',
            'field_schema',
            'configuration',
            'is_system_managed',
            'is_active',
        ])
        ->keyBy('stable_key');

    expect($enumKeys)->toContain(...SOURCE_TOPIC_TYPE_KEYS)
        ->and($definitions)->toHaveCount(count(SOURCE_TOPIC_TYPE_KEYS));

    foreach (SOURCE_TOPIC_TYPE_KEYS as $stableKey) {
        $definition = $definitions->get($stableKey);

        expect($definition)
            ->toBeInstanceOf(ForumTopicTypeModel::class)
            ->and($definition->stable_key)->toBe($stableKey)
            ->and($definition->schema_version)->toBeGreaterThanOrEqual(1)
            ->and($definition->field_schema)->toBeArray()
            ->and($definition->field_schema)->toHaveKeys(['title', 'body', 'category'])
            ->and($definition->configuration)->toBeArray()
            ->and($definition->is_system_managed)->toBeTrue()
            ->and($definition->is_active)->toBeTrue();

        foreach (['en', 'lt', 'ru'] as $locale) {
            expect(Lang::has($definition->name_translation_key, $locale))->toBeTrue()
                ->and(Lang::has($definition->description_translation_key, $locale))->toBeTrue();
        }
    }
});

test('topic type persistence uses normalized identity and versioned structured json', function () {
    $topicTypeColumns = Schema::getColumnListing('forum_topic_types');
    $topicColumns = Schema::getColumnListing('forum_topics');
    $topicForeignKeys = collect(Schema::getForeignKeys('forum_topics'));

    expect($topicTypeColumns)->toContain(
        'id',
        'stable_key',
        'schema_version',
        'field_schema',
        'configuration',
    )
        ->and(Schema::hasIndex('forum_topic_types', 'forum_topic_types_stable_key_unique'))
        ->toBeTrue()
        ->and($topicColumns)->toContain(
            'forum_topic_type_id',
            'structured_data',
            'structured_data_version',
        )
        ->and($topicColumns)->not->toContain(
            'journal_type',
            'source_url',
            'expires_at',
            'starts_at',
            'ends_at',
            'observation_location',
            'observed_at',
        )
        ->and(Schema::hasIndex('forum_topics', 'forum_topics_normalized_type_status_idx'))
        ->toBeTrue()
        ->and($topicForeignKeys->contains(
            static fn (array $foreignKey): bool => $foreignKey['columns'] === ['forum_topic_type_id']
                && $foreignKey['foreign_table'] === 'forum_topic_types',
        ))->toBeTrue();
});

test('repeated topic type synchronization preserves ids relations and custom definitions', function () {
    $this->seed(ForumTopicTypeSeeder::class);

    $discussion = ForumTopicTypeModel::query()
        ->where('stable_key', ForumTopicType::Discussion->value)
        ->firstOrFail(['id', 'stable_key']);
    $custom = ForumTopicTypeModel::factory()->create([
        'stable_key' => 'community-observation',
        'is_system_managed' => false,
    ]);
    $topic = ForumTopic::factory()->create([
        'type' => ForumTopicType::Discussion,
        'forum_topic_type_id' => $discussion->id,
        'structured_data' => ['animal_context' => 'taxa'],
        'structured_data_version' => 1,
    ]);

    $this->seed(ForumTopicTypeSeeder::class);

    $reloadedDiscussion = ForumTopicTypeModel::query()
        ->where('stable_key', ForumTopicType::Discussion->value)
        ->firstOrFail(['id']);
    $reloadedTopic = ForumTopic::query()->findOrFail($topic->id);

    expect($reloadedDiscussion->id)->toBe($discussion->id)
        ->and($reloadedTopic->forum_topic_type_id)->toBe($discussion->id)
        ->and($reloadedTopic->structured_data)->toBe(['animal_context' => 'taxa'])
        ->and($reloadedTopic->structured_data_version)->toBe(1)
        ->and(ForumTopicTypeModel::query()->whereKey($custom->id)->exists())->toBeTrue();
});
