<?php

declare(strict_types=1);

use App\Actions\AcceptForumAnswer;
use App\Actions\PerformForumAction;
use App\Actions\RecordAnswerVote;
use App\Enums\ForumTopicType;
use App\Enums\ForumVoteValue;
use App\Models\ForumAnswer;
use App\Models\ForumTopic;
use App\Models\ForumTopicType as ForumTopicTypeModel;
use App\Models\ForumVote;
use App\Services\ForumTopicTypeSchemaCatalog;
use App\Services\ForumTopicTypeSchemaRegistry;
use Database\Seeders\ForumTopicTypeSeeder;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

const RUNTIME_SCHEMA_SOURCE_TOPIC_TYPES = [
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

test('system topic types expose complete stable versioned schema capabilities', function () {
    $this->seed(ForumTopicTypeSeeder::class);

    $definitions = ForumTopicTypeModel::query()
        ->whereIn('stable_key', RUNTIME_SCHEMA_SOURCE_TOPIC_TYPES)
        ->orderBy('stable_key')
        ->get([
            'id',
            'stable_key',
            'name_translation_key',
            'schema_version',
            'field_schema',
            'configuration',
            'allows_accepted_answers',
            'expires',
        ]);

    expect($definitions)->toHaveCount(count(RUNTIME_SCHEMA_SOURCE_TOPIC_TYPES))
        ->and(Schema::getColumnListing('forum_topic_types'))
        ->not->toContain('external_id', 'external_source_id', 'translated_name')
        ->and(Schema::hasIndex(
            'forum_topic_types',
            'forum_topic_types_stable_key_unique',
        ))->toBeTrue();

    foreach ($definitions as $definition) {
        expect($definition->getKey())->toBeInt()
            ->and($definition->stable_key)->toBeString()->not->toBe('')
            ->and($definition->name_translation_key)
            ->toBe("forum.topic_types.{$definition->stable_key}.name")
            ->and($definition->schema_version)->toBeGreaterThanOrEqual(1)
            ->and($definition->field_schema)->toBeArray()->not->toBeEmpty()
            ->and(collect($definition->field_schema)->contains(
                static fn (array $field): bool => $field['required'] === true,
            ))->toBeTrue()
            ->and(collect($definition->field_schema)->contains(
                static fn (array $field): bool => $field['required'] === false,
            ))->toBeTrue();

        foreach ($definition->field_schema as $field) {
            expect($field)->toHaveKeys(['type', 'required', 'validation'])
                ->and($field['validation'])->toBeArray();
        }

        expect($definition->configuration)->toHaveKeys([
            'expiration',
            'archival',
            'requires_location',
            'requires_species',
            'contact_restriction',
            'allowed_attachments',
            'allowed_reactions',
            'accepted_answers',
            'seo',
            'notifications',
        ])
            ->and($definition->configuration['accepted_answers']['enabled'])
            ->toBe($definition->allows_accepted_answers)
            ->and($definition->configuration['expiration']['enabled'])
            ->toBe($definition->expires)
            ->and($definition->configuration['archival'])
            ->toHaveKeys(['enabled', 'inactive_days', 'action'])
            ->and($definition->configuration['contact_restriction'])
            ->toHaveKeys(['mode', 'allow_direct_contact_fields'])
            ->and($definition->configuration['allowed_attachments'])
            ->toBeArray()->not->toBeEmpty()
            ->and($definition->configuration['allowed_reactions'])
            ->toBeArray()->not->toBeEmpty()
            ->and($definition->configuration['seo']['indexable'])->toBeBool()
            ->and($definition->configuration['notifications']['levels'])
            ->toBeArray()->not->toBeEmpty();
    }
});

test('schema registry uses the immutable catalogue before definitions are seeded', function () {
    Cache::forget(ForumTopicTypeSchemaRegistry::CACHE_KEY);

    $definition = app(ForumTopicTypeSchemaRegistry::class)
        ->definition(ForumTopicType::Question->value);

    expect($definition)->not->toBeNull()
        ->and($definition?->databaseId)->toBeNull()
        ->and($definition?->stableKey)->toBe(ForumTopicType::Question->value);
});

test('schema registry fails open to its bounded source when cache is unavailable', function () {
    $cache = Mockery::mock(CacheRepository::class);
    $cache->shouldReceive('get')
        ->once()
        ->andThrow(new RuntimeException('cache unavailable'));
    $registry = new ForumTopicTypeSchemaRegistry(
        app(ForumTopicTypeSchemaCatalog::class),
        $cache,
    );

    expect($registry->definition(ForumTopicType::Question->value))
        ->not->toBeNull()
        ->stableKey->toBe(ForumTopicType::Question->value);
});

test('schema registry caches one bounded query and invalidates after writes and synchronization', function () {
    $this->seed(ForumTopicTypeSeeder::class);
    Cache::forget(ForumTopicTypeSchemaRegistry::CACHE_KEY);
    $model = ForumTopicTypeModel::query()
        ->where('stable_key', ForumTopicType::Question->value)
        ->firstOrFail();
    $definitionQueries = 0;
    $definitionSql = [];

    DB::listen(function (QueryExecuted $query) use (
        &$definitionQueries,
        &$definitionSql,
    ): void {
        if (
            str_starts_with(strtolower(ltrim($query->sql)), 'select')
            && str_contains($query->sql, 'forum_topic_types')
        ) {
            $definitionQueries++;
            $definitionSql[] = strtolower($query->sql);
        }
    });

    $registry = app(ForumTopicTypeSchemaRegistry::class);
    $first = $registry->definition(ForumTopicType::Question->value);
    $second = $registry->definition(ForumTopicType::Question->value);

    expect($first)->not->toBeNull()
        ->and($second?->stableKey)->toBe(ForumTopicType::Question->value)
        ->and($definitionQueries)->toBe(1)
        ->and($definitionSql[0])->toContain('limit 200')
        ->and(Cache::has(ForumTopicTypeSchemaRegistry::CACHE_KEY))->toBeTrue();

    $model->update(['schema_version' => 2]);

    expect(Cache::has(ForumTopicTypeSchemaRegistry::CACHE_KEY))->toBeFalse()
        ->and($registry->definition(ForumTopicType::Question->value)?->schemaVersion)
        ->toBe(2)
        ->and($definitionQueries)->toBe(2);

    $model->delete();

    expect(Cache::has(ForumTopicTypeSchemaRegistry::CACHE_KEY))->toBeFalse()
        ->and($registry->definition(ForumTopicType::Question->value))->toBeNull()
        ->and($definitionQueries)->toBe(3);

    Cache::put(ForumTopicTypeSchemaRegistry::CACHE_KEY, ['stale' => true], 600);
    $this->seed(ForumTopicTypeSeeder::class);

    expect(Cache::has(ForumTopicTypeSchemaRegistry::CACHE_KEY))->toBeFalse();
});

test('topic create and update enforce active schema context attachments and version', function () {
    Storage::fake('public');
    $this->seed(ForumTopicTypeSeeder::class);
    $definition = ForumTopicTypeModel::query()
        ->where('stable_key', ForumTopicType::Question->value)
        ->firstOrFail();
    $configuration = $definition->configuration;
    $configuration['requires_location'] = true;
    $configuration['requires_species'] = true;
    $configuration['allowed_attachments'] = ['image'];
    $definition->update([
        'schema_version' => 3,
        'configuration' => $configuration,
    ]);

    $this->from(route('forum.topics.create'))
        ->post(route('forum.topics.store'), runtimeSchemaTopicPayload([
            'pet_key' => null,
            'location' => null,
            'video' => UploadedFile::fake()->create(
                'unsafe-for-this-type.mp4',
                128,
                'video/mp4',
            ),
        ]))
        ->assertRedirect(route('forum.topics.create'))
        ->assertSessionHasErrors(['location', 'taxon_ids', 'video']);

    $this->post(route('forum.topics.store'), runtimeSchemaTopicPayload([
        'pet_key' => 'scout',
        'location' => 'Vilnius',
        'photos' => [UploadedFile::fake()->image('context.jpg', 64, 64)],
        'photo_alt' => 'Birch resting on a blanket after a sound exercise.',
    ]))->assertRedirect();

    $topic = ForumTopic::query()->firstOrFail();

    expect($topic->forum_topic_type_id)->toBe($definition->id)
        ->and($topic->structured_data_version)->toBe(3)
        ->and(data_get($topic->structured_data, 'topic_type_key'))
        ->toBe(ForumTopicType::Question->value)
        ->and(data_get($topic->media, '0.type'))->toBe('image');

    $definition->update(['schema_version' => 4]);
    $this->put(route('forum.topics.update', $topic), runtimeSchemaTopicPayload([
        'pet_key' => 'scout',
        'location' => 'Kaunas',
        'title' => 'How can I keep this updated question useful over time?',
    ]))->assertRedirect(route('forum.topics.show', $topic));

    expect($topic->refresh()->structured_data_version)->toBe(4)
        ->and($topic->location)->toBe('Kaunas');

    $definition->update(['is_active' => false]);

    $this->from(route('forum.topics.create'))
        ->post(route('forum.topics.store'), runtimeSchemaTopicPayload())
        ->assertRedirect(route('forum.topics.create'))
        ->assertSessionHasErrors('type');
});

test('direct rating acceptance and notification mutations obey the current topic type schema', function () {
    $this->seed(ForumTopicTypeSeeder::class);
    $questionDefinition = ForumTopicTypeModel::query()
        ->where('stable_key', ForumTopicType::Question->value)
        ->firstOrFail();
    $configuration = $questionDefinition->configuration;
    $configuration['allowed_reactions'] = ['needs-source'];
    $configuration['notifications']['levels'] = ['mentions', 'none'];
    $questionDefinition->update(['configuration' => $configuration]);
    $question = ForumTopic::factory()->create([
        'author_id' => $this->authenticatedUser->id,
        'author_key' => $this->authenticatedUser->actor_key,
        'type' => ForumTopicType::Question,
        'forum_topic_type_id' => $questionDefinition->id,
    ]);
    $answer = ForumAnswer::factory()->create(['topic_id' => $question->id]);

    expect(fn () => app(RecordAnswerVote::class)->handle(
        $answer->id,
        'helpful',
        null,
    ))->toThrow(ValidationException::class)
        ->and(fn () => app(PerformForumAction::class)->handle([
            'action' => 'set-subscription',
            'topic_id' => $question->id,
            'value' => 'all',
        ]))->toThrow(ValidationException::class);

    app(RecordAnswerVote::class)->handle($answer->id, 'needs-source', null);
    app(PerformForumAction::class)->handle([
        'action' => 'set-subscription',
        'topic_id' => $question->id,
        'value' => 'mentions',
    ]);

    expect(ForumVote::query()->where('answer_id', $answer->id)->value('value'))
        ->toBe(ForumVoteValue::NeedsSource);

    $discussionDefinition = ForumTopicTypeModel::query()
        ->where('stable_key', ForumTopicType::Discussion->value)
        ->firstOrFail();
    $discussion = ForumTopic::factory()->create([
        'author_id' => $this->authenticatedUser->id,
        'author_key' => $this->authenticatedUser->actor_key,
        'type' => ForumTopicType::Discussion,
        'forum_topic_type_id' => $discussionDefinition->id,
    ]);
    $discussionAnswer = ForumAnswer::factory()->create([
        'topic_id' => $discussion->id,
    ]);

    expect(fn () => app(AcceptForumAnswer::class)->handle(
        $discussionAnswer->id,
    ))->toThrow(ValidationException::class);
});

/** @param array<string, mixed> $overrides */
function runtimeSchemaTopicPayload(array $overrides = []): array
{
    return [
        'type' => 'question',
        'category' => 'behavior',
        'subcategory' => 'fear',
        'pet_key' => 'scout',
        'title' => 'How can I help Birch stay calm around unfamiliar sounds?',
        'body' => 'Birch becomes worried after sudden metallic sounds. I want a gradual plan that preserves distance, choice, and recovery while we track his response.',
        'desired_answer' => 'step-by-step',
        'tags' => 'fear, sound, recovery',
        'location' => 'Vilnius',
        'visibility' => 'public',
        'comment_policy' => 'registered',
        'language' => 'en',
        'intent' => 'publish',
        ...$overrides,
    ];
}
