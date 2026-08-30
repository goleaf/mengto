<?php

declare(strict_types=1);

use App\Actions\CreateAnswer;
use App\Actions\CreateKnowledgeGuideTranslation;
use App\Data\KnowledgeGuideData;
use App\Enums\KnowledgeStatus;
use App\Enums\KnowledgeTranslationSource;
use App\Livewire\Forum\AnimalTaxonomySelector;
use App\Livewire\Forum\KnowledgeGuideEditor;
use App\Models\CommunityAnimalGroup;
use App\Models\ForumBadge;
use App\Models\ForumCategory;
use App\Models\ForumModerationActionDefinition;
use App\Models\ForumNotification;
use App\Models\ForumReportReason;
use App\Models\ForumReputationDimension;
use App\Models\ForumTopic;
use App\Models\ForumTopicType;
use App\Models\ForumTrustLevel;
use App\Models\ForumUserTrustLevel;
use App\Models\KnowledgeArticle;
use App\Models\Taxon;
use App\Models\TaxonName;
use App\Models\User;
use App\Services\ForumCategoryTree;
use App\Services\LocalizedTaxonName;
use Database\Seeders\ForumSystemSeeder;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(ForumSystemSeeder::class);
});

test('knowledge translations have additive provenance and lookup indexes', function () {
    expect(Schema::hasColumns('knowledge_articles', [
        'translated_from_article_id',
        'translated_by_user_id',
        'translation_source',
    ]))->toBeTrue()
        ->and(Schema::hasIndex(
            'knowledge_articles',
            'knowledge_articles_translation_source_locale_status_idx',
        ))->toBeTrue()
        ->and(Schema::hasIndex(
            'knowledge_articles',
            'knowledge_articles_translator_source_created_idx',
        ))->toBeTrue();
});

test('every seeded forum definition resolves through every supported locale', function () {
    $keys = collect()
        ->concat(ForumTopicType::query()
            ->get(['name_translation_key', 'description_translation_key'])
            ->flatMap(fn (ForumTopicType $type): array => [
                $type->name_translation_key,
                $type->description_translation_key,
            ]))
        ->concat(ForumReportReason::query()->pluck('translation_key'))
        ->concat(ForumModerationActionDefinition::query()->pluck('translation_key'))
        ->concat(ForumReputationDimension::query()
            ->get(['name_translation_key', 'description_translation_key'])
            ->flatMap(fn (ForumReputationDimension $dimension): array => [
                $dimension->name_translation_key,
                $dimension->description_translation_key,
            ]))
        ->concat(ForumTrustLevel::query()
            ->get(['name_translation_key', 'description_translation_key'])
            ->flatMap(fn (ForumTrustLevel $level): array => [
                $level->name_translation_key,
                $level->description_translation_key,
            ]))
        ->concat(ForumBadge::query()
            ->get(['name_translation_key', 'description_translation_key'])
            ->flatMap(fn (ForumBadge $badge): array => [
                $badge->name_translation_key,
                $badge->description_translation_key,
            ]))
        ->concat(CommunityAnimalGroup::query()
            ->get(['name_translation_key', 'description_translation_key'])
            ->flatMap(fn (CommunityAnimalGroup $group): array => [
                $group->name_translation_key,
                $group->description_translation_key,
            ]))
        ->push(
            'forum_moderation.appeal_statuses.submitted',
            'forum_moderation.appeal_statuses.appeal-review',
            'forum_moderation.appeal_statuses.upheld',
            'forum_moderation.appeal_statuses.modified',
            'forum_moderation.appeal_statuses.reversed',
            'forum_moderation.appeal_statuses.new-review',
        )
        ->filter()
        ->unique()
        ->values();

    foreach (config('platform.supported_locales', ['en']) as $locale) {
        foreach ($keys as $key) {
            expect(trans((string) $key, locale: $locale))
                ->not->toBe($key)
                ->not->toBe('');
        }
    }

    expect(trans('forum_moderation.reasons.animal-cruelty', locale: 'lt'))
        ->not->toBe(trans('forum_moderation.reasons.animal-cruelty', locale: 'en'))
        ->and(trans('forum_moderation.actions.content-removal', locale: 'ru'))
        ->not->toBe(trans('forum_moderation.actions.content-removal', locale: 'en'));
});

test('the forum page title has reviewed values in every supported locale', function () {
    expect(trans(
        'messages.forum_and_knowledge_brand',
        locale: 'en',
    ))->toBe('Forum and knowledge | PawCircle')
        ->and(trans(
            'messages.forum_and_knowledge_brand',
            locale: 'lt',
        ))->toBe('Forumas ir žinių bazė | PawCircle')
        ->and(trans(
            'messages.forum_and_knowledge_brand',
            locale: 'ru',
        ))->toBe('Форум и база знаний | PawCircle');
});

test('every system category has a locale row and deterministic fallback content', function () {
    $locales = config('platform.supported_locales', ['en']);
    $categories = ForumCategory::query()
        ->select(['id', 'parent_id', 'stable_key'])
        ->where('is_system_managed', true)
        ->with('translations:id,forum_category_id,locale,name,description,is_reviewed')
        ->get();

    expect($categories)->not->toBeEmpty();

    foreach ($categories as $category) {
        expect($category->translations->pluck('locale')->sort()->values()->all())
            ->toBe(collect($locales)->sort()->values()->all())
            ->and($category->translations->every(
                static fn ($translation): bool => trim($translation->name) !== '',
            ))->toBeTrue();

        if ($category->parent_id === null) {
            expect($category->translations->every(
                static fn ($translation): bool => trim((string) $translation->description) !== '',
            ))->toBeTrue()
                ->and($category->translations->every(
                    static fn ($translation): bool => $translation->is_reviewed,
                ))->toBeTrue();
        }
    }

    $health = $categories->firstWhere('stable_key', 'forum.health');
    $descriptions = $health?->translations->pluck('description', 'locale');

    expect($descriptions?->get('lt'))->not->toBe($descriptions?->get('en'))
        ->and($descriptions?->get('ru'))->not->toBe($descriptions?->get('en'));
});

test('category synchronization invalidates every locale-scoped tree cache', function () {
    foreach (config('platform.supported_locales', ['en']) as $locale) {
        foreach (ForumCategoryTree::cacheKeysForLocale($locale) as $key) {
            Cache::put($key, ['stale' => true], 600);
        }
    }

    $this->seed(ForumSystemSeeder::class);

    foreach (config('platform.supported_locales', ['en']) as $locale) {
        foreach (ForumCategoryTree::cacheKeysForLocale($locale) as $key) {
            expect(Cache::has($key))->toBeFalse();
        }
    }
});

test('forum notifications are rendered in the recipient locale', function () {
    $recipient = User::factory()->create(['locale' => 'ru']);
    $answerer = User::factory()->create(['locale' => 'en']);
    $topic = ForumTopic::factory()->create([
        'author_key' => $recipient->actor_key,
        'author_name' => $recipient->name,
        'language' => 'ru',
    ]);
    $this->actingAs($answerer);

    app(CreateAnswer::class)->handle($topic, [
        'body' => 'A complete answer whose notification belongs to the recipient locale.',
        'experience_type' => 'personal-experience',
        'sources' => '',
    ]);

    $notification = ForumNotification::query()
        ->where('user_key', $recipient->actor_key)
        ->firstOrFail();

    expect($notification->title)
        ->toBe(trans('messages.new_answer_added', locale: 'ru'))
        ->not->toBe(trans('messages.new_answer_added', locale: 'en'))
        ->and($notification->body)
        ->toContain($answerer->name);

    $this->actingAs($recipient)
        ->get(route('forum.index'))
        ->assertSuccessful()
        ->assertSee($notification->title);
});

test('a human guide translation preserves the original and records source provenance', function () {
    $administrator = User::factory()->administrator()->create();
    $source = KnowledgeArticle::factory()->create([
        'title' => 'Original dog handling guide',
        'summary' => 'Original summary that must remain unchanged after a translation is created.',
        'body' => 'Original body that remains available in English without silent replacement.',
        'language' => 'en',
        'sources' => ['https://example.test/original-source'],
    ]);
    $original = $source->only(['title', 'summary', 'body', 'language']);
    $originalUpdatedAt = $source->updated_at?->toISOString();

    $translation = app(CreateKnowledgeGuideTranslation::class)->handle(
        $administrator,
        $source,
        multilingualGuideData([
            'title' => 'Saugus šuns vedimas',
            'summary' => 'Lietuviškas bendruomenės vadovo vertimas su aiškia kilme.',
            'body' => str_repeat(
                'Vertimo tekstas saugomas atskirai nuo originalaus vadovo. ',
                3,
            ),
            'language' => 'lt',
        ]),
    );

    $freshSource = $source->fresh();

    expect($freshSource?->only(array_keys($original)))->toBe($original)
        ->and($freshSource?->updated_at?->toISOString())->toBe($originalUpdatedAt)
        ->and($translation->status)->toBe(KnowledgeStatus::Draft)
        ->and($translation->translated_from_article_id)->toBe($source->id)
        ->and($translation->translated_by_user_id)->toBe($administrator->id)
        ->and($translation->translation_source)
        ->toBe(KnowledgeTranslationSource::HumanCommunity)
        ->and($translation->translation_group_key)->toBe($source->translation_group_key)
        ->and($translation->language)->toBe('lt')
        ->and($translation->body)->not->toBe($source->body)
        ->and($translation->versions()->count())->toBe(1)
        ->and($translation->workflowEvents()
            ->where('reason_code', 'guide-translation-created')
            ->exists())->toBeTrue();

    $translation->forceFill([
        'status' => KnowledgeStatus::Published,
        'published_at' => now(),
    ])->save();

    $this->get(route('knowledge.articles.show', $translation))
        ->assertOk()
        ->assertSee('Human community translation')
        ->assertSee($source->title)
        ->assertSee($administrator->name)
        ->assertSee('This translation has its own version history and correction path.');
});

test('a public translation does not disclose its private source guide', function () {
    $administrator = User::factory()->administrator()->create();
    $source = KnowledgeArticle::factory()->draft()->create([
        'title' => 'Private editorial source title',
        'language' => 'en',
    ]);
    $translation = app(CreateKnowledgeGuideTranslation::class)->handle(
        $administrator,
        $source,
        multilingualGuideData(['language' => 'lt']),
    );
    $translation->forceFill([
        'status' => KnowledgeStatus::Published,
        'published_at' => now(),
    ])->save();

    $this->get(route('knowledge.articles.show', $translation))
        ->assertOk()
        ->assertDontSee($source->title)
        ->assertSee('Human community translation');
});

test('translation locale uniqueness and source-language validation are enforced', function () {
    $administrator = User::factory()->administrator()->create();
    $source = KnowledgeArticle::factory()->create(['language' => 'en']);
    $action = app(CreateKnowledgeGuideTranslation::class);

    expect(fn () => $action->handle(
        $administrator,
        $source,
        multilingualGuideData(['language' => 'en']),
    ))->toThrow(ValidationException::class);

    $action->handle(
        $administrator,
        $source,
        multilingualGuideData(['language' => 'lt']),
    );

    expect(fn () => $action->handle(
        $administrator,
        $source,
        multilingualGuideData(['language' => 'lt']),
    ))->toThrow(ValidationException::class);
});

test('private draft content cannot be translated without source update authority', function () {
    $member = User::factory()->create();
    ForumUserTrustLevel::factory()->create([
        'user_id' => $member->id,
        'forum_trust_level_id' => ForumTrustLevel::query()
            ->where('stable_key', 'trusted-contributor')
            ->valueOrFail('id'),
    ]);
    $privateDraft = KnowledgeArticle::factory()->draft()->create();

    expect(fn () => app(CreateKnowledgeGuideTranslation::class)->handle(
        $member,
        $privateDraft,
        multilingualGuideData(['language' => 'lt']),
    ))->toThrow(AuthorizationException::class);

    $this->actingAs($member)
        ->get(route('knowledge.guides.translations.create', $privateDraft))
        ->assertForbidden();
});

test('a guide without a translation family cannot open a translation editor', function () {
    $trustedContributor = User::factory()->create();
    ForumUserTrustLevel::factory()->create([
        'user_id' => $trustedContributor->id,
        'forum_trust_level_id' => ForumTrustLevel::query()
            ->where('stable_key', 'trusted-contributor')
            ->valueOrFail('id'),
    ]);
    $standaloneGuide = KnowledgeArticle::factory()->create([
        'translation_group_key' => null,
    ]);

    $this->actingAs($trustedContributor)
        ->get(route('knowledge.guides.translations.create', $standaloneGuide))
        ->assertForbidden();
});

test('the livewire translation editor keeps source identity locked and prose empty', function () {
    $administrator = User::factory()->administrator()->create();
    $source = KnowledgeArticle::factory()->create([
        'title' => 'Source guidance remains visible',
        'body' => 'Private source prose must not be silently copied into a translation form.',
        'language' => 'en',
    ]);
    $other = KnowledgeArticle::factory()->create();

    $component = Livewire::actingAs($administrator)
        ->test(KnowledgeGuideEditor::class, ['sourceArticleId' => $source->id])
        ->assertSet('sourceArticleId', $source->id)
        ->assertSet('form.title', '')
        ->assertSet('form.summary', '')
        ->assertSet('form.body', '')
        ->assertSee($source->title)
        ->assertSee('Translation source');

    expect(fn () => $component->set('sourceArticleId', $other->id))
        ->toThrow(CannotUpdateLockedPropertyException::class);
});

test('the livewire translation editor creates a separate attributed draft', function () {
    $administrator = User::factory()->administrator()->create();
    $source = KnowledgeArticle::factory()->create([
        'title' => 'Source guide for a connected translation flow',
        'category' => 'behavior',
        'language' => 'en',
    ]);

    $component = Livewire::actingAs($administrator)
        ->test(KnowledgeGuideEditor::class, ['sourceArticleId' => $source->id])
        ->assertSet('form.language', 'lt')
        ->set('form.title', 'Susietas gyvūnų priežiūros vadovo vertimas')
        ->set(
            'form.summary',
            'Atskiras lietuviškas vertimas su išsaugotu originalu ir aiškia kilme.',
        )
        ->set('form.body', str_repeat(
            'Vertimo turinys saugomas atskirai ir nekeičia originalaus vadovo. ',
            3,
        ))
        ->set('form.sourcesText', 'https://example.test/connected-translation')
        ->set('form.changeSummary', 'Sukurtas pradinis žmogaus parengtas vertimas.')
        ->call('save')
        ->assertHasNoErrors();

    $translation = KnowledgeArticle::query()
        ->where('translated_from_article_id', $source->id)
        ->where('language', 'lt')
        ->firstOrFail();

    $component->assertRedirect(route('knowledge.guides.edit', $translation));

    expect($translation->status)->toBe(KnowledgeStatus::Draft)
        ->and($translation->translated_by_user_id)->toBe($administrator->id)
        ->and($translation->translation_source)
        ->toBe(KnowledgeTranslationSource::HumanCommunity)
        ->and($translation->body)->not->toBe($source->body)
        ->and($source->fresh()?->title)->toBe($source->title);
});

test('the translate action is hidden when every supported locale already exists', function () {
    $administrator = User::factory()->administrator()->create();
    $english = KnowledgeArticle::factory()->create(['language' => 'en']);
    KnowledgeArticle::factory()
        ->translatedFrom($english, $administrator, 'lt')
        ->create();
    KnowledgeArticle::factory()
        ->draft()
        ->translatedFrom($english, $administrator, 'ru')
        ->create();

    $this->actingAs($administrator)
        ->get(route('knowledge.articles.show', $english))
        ->assertOk()
        ->assertDontSee(route('knowledge.guides.translations.create', $english), false);
});

test('verified common names follow locale fallback while scientific names remain exact', function () {
    $taxon = Taxon::query()
        ->where('stable_key', 'taxon.core.canis-lupus-familiaris')
        ->firstOrFail();
    TaxonName::factory()->create([
        'taxon_id' => $taxon->id,
        'locale' => 'lt',
        'language' => 'Lithuanian',
        'name' => 'Naminis šuo',
        'normalized_name' => 'naminis šuo',
        'name_type' => 'preferred common',
        'is_preferred' => true,
        'is_verified' => true,
    ]);
    TaxonName::factory()->create([
        'taxon_id' => $taxon->id,
        'locale' => 'ru',
        'language' => 'Russian',
        'name' => 'Непроверенное имя',
        'normalized_name' => 'непроверенное имя',
        'name_type' => 'preferred common',
        'is_preferred' => true,
        'is_verified' => false,
    ]);
    $taxon->load([
        'activeVersion:id,taxon_id,rank,scientific_name,is_active_version',
        'names:id,taxon_id,locale,name,name_type,is_preferred,is_verified,is_active',
    ]);
    $presenter = app(LocalizedTaxonName::class);
    $scientificName = $taxon->activeVersion?->scientific_name;

    expect($presenter->present($taxon, 'lt', 'en'))
        ->toMatchArray([
            'name' => 'Naminis šuo',
            'scientific_name' => $scientificName,
            'name_locale' => 'lt',
        ])
        ->and($presenter->present($taxon, 'ru', 'en'))
        ->toMatchArray([
            'name' => 'Domestic dog',
            'scientific_name' => $scientificName,
            'name_locale' => 'en',
        ]);

    foreach (config('platform.supported_locales', ['en']) as $locale) {
        App::setLocale($locale);
        Livewire::test(AnimalTaxonomySelector::class, [
            'selected' => [$taxon->id],
        ])->assertSee((string) $scientificName);
    }
});

test('an unidentified taxon fallback uses the explicitly requested locale', function () {
    App::setLocale('en');
    $taxon = Taxon::factory()->create();
    $taxon->load(['activeVersion', 'names']);

    expect(app(LocalizedTaxonName::class)->present($taxon, 'ru', 'en'))
        ->toMatchArray([
            'name' => 'Неидентифицированное животное',
            'scientific_name' => null,
            'name_locale' => null,
        ]);
});

/**
 * @param  array<string, mixed>  $overrides
 */
function multilingualGuideData(array $overrides = []): KnowledgeGuideData
{
    return new KnowledgeGuideData(
        title: (string) ($overrides['title'] ?? 'Translated animal care guide'),
        summary: (string) ($overrides['summary']
            ?? 'A complete translated summary with explicit human provenance.'),
        body: (string) ($overrides['body'] ?? str_repeat(
            'Translated guidance remains separate from the original content. ',
            3,
        )),
        category: (string) ($overrides['category'] ?? 'behavior'),
        type: (string) ($overrides['type'] ?? 'guide'),
        difficulty: (string) ($overrides['difficulty'] ?? 'beginner'),
        audience: $overrides['audience'] ?? 'Animal owners',
        language: (string) ($overrides['language'] ?? 'lt'),
        jurisdiction: $overrides['jurisdiction'] ?? null,
        taxonId: $overrides['taxonId'] ?? null,
        discussionTopicId: $overrides['discussionTopicId'] ?? null,
        sources: $overrides['sources'] ?? ['https://example.test/translation-source'],
        protectedSections: $overrides['protectedSections'] ?? [],
        changeSummary: (string) ($overrides['changeSummary']
            ?? 'Created the initial human translation draft.'),
        expectedLockVersion: 0,
    );
}
