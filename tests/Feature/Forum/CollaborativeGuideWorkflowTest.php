<?php

declare(strict_types=1);

use App\Actions\CreateKnowledgeGuide;
use App\Actions\ManageKnowledgeCollaborator;
use App\Actions\ReviewKnowledgeCorrection;
use App\Actions\RollbackKnowledgeGuideVersion;
use App\Actions\SaveKnowledgeGuideRevision;
use App\Actions\SetKnowledgeEditorialLock;
use App\Actions\TransitionKnowledgeGuide;
use App\Data\KnowledgeGuideData;
use App\Enums\KnowledgeCollaboratorRole;
use App\Enums\KnowledgeCorrectionStatus;
use App\Enums\KnowledgeStatus;
use App\Enums\KnowledgeWorkflowEventType;
use App\Enums\VerificationStatus;
use App\Livewire\Forum\AdminDashboard;
use App\Livewire\Forum\KnowledgeGuideEditor;
use App\Models\ExpertProfile;
use App\Models\ForumAnswer;
use App\Models\ForumTopic;
use App\Models\ForumTrustLevel;
use App\Models\ForumUserTrustLevel;
use App\Models\ForumVote;
use App\Models\KnowledgeArticle;
use App\Models\KnowledgeArticleCollaborator;
use App\Models\KnowledgeCorrection;
use App\Models\User;
use App\Services\KnowledgePresenter;
use Database\Seeders\ForumSystemSeeder;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(ForumSystemSeeder::class);
});

test('collaborative guide schema has scoped integrity and workflow indexes', function () {
    expect(Schema::hasColumns('knowledge_articles', [
        'created_by_user_id',
        'translation_group_key',
        'jurisdiction',
        'taxon_id',
        'discussion_topic_id',
        'replaced_by_article_id',
        'protected_sections',
        'editorial_locked_at',
        'editorial_locked_by_user_id',
        'editorial_lock_reason',
        'lock_version',
    ]))->toBeTrue()
        ->and(Schema::hasIndex(
            'knowledge_articles',
            'knowledge_articles_translation_locale_unique',
        ))->toBeTrue()
        ->and(Schema::hasIndex(
            'knowledge_articles',
            'knowledge_articles_status_language_review_idx',
        ))->toBeTrue()
        ->and(Schema::hasIndex(
            'knowledge_article_collaborators',
            'knowledge_collaborators_article_user_role_unique',
        ))->toBeTrue()
        ->and(Schema::hasIndex(
            'knowledge_workflow_events',
            'knowledge_workflow_events_article_created_idx',
        ))->toBeTrue();
});

test('the complete guide state graph and public visibility are explicit', function () {
    $expectedTransitions = [
        'draft' => ['submitted-for-review', 'archived'],
        'submitted-for-review' => ['changes-requested', 'community-reviewed', 'expert-reviewed'],
        'changes-requested' => ['submitted-for-review', 'archived'],
        'community-reviewed' => ['changes-requested', 'expert-reviewed', 'published'],
        'expert-reviewed' => ['changes-requested', 'published'],
        'published' => ['correction-requested', 'outdated', 'archived', 'replaced'],
        'correction-requested' => ['submitted-for-review', 'archived'],
        'outdated' => ['submitted-for-review', 'archived', 'replaced'],
        'archived' => ['draft'],
        'replaced' => ['archived'],
    ];

    foreach (KnowledgeStatus::cases() as $status) {
        expect(array_map(
            static fn (KnowledgeStatus $target): string => $target->value,
            $status->allowedTransitions(),
        ))->toBe($expectedTransitions[$status->value]);

        $article = KnowledgeArticle::factory()->create([
            'status' => $status,
            'published_at' => $status->isPublic() ? now() : null,
        ]);
        $response = $this->get(route('knowledge.articles.show', $article));

        if ($status->isPublic()) {
            $response->assertOk();
        } else {
            $response->assertNotFound();
        }
    }
});

test('an invalid direct workflow transition is rejected without side effects', function () {
    $administrator = User::factory()->administrator()->create();
    $article = app(CreateKnowledgeGuide::class)->handle(
        $administrator,
        collaborativeGuideData(),
    );

    expect(fn () => app(TransitionKnowledgeGuide::class)->handle(
        $administrator,
        $article,
        KnowledgeStatus::Published,
        'A draft cannot bypass the independent review workflow.',
        $article->lock_version,
    ))->toThrow(ValidationException::class);

    expect($article->refresh()->status)->toBe(KnowledgeStatus::Draft)
        ->and($article->workflowEvents()->count())->toBe(1);
});

test('popular forum content is never converted into official guidance automatically', function () {
    $topic = ForumTopic::factory()->resolved()->create([
        'author_id' => $this->authenticatedUser->id,
        'author_key' => 'mia-carter',
    ]);
    $answer = ForumAnswer::factory()->create([
        'topic_id' => $topic->id,
        'is_accepted' => true,
        'helpful_count' => 40,
    ]);
    ForumVote::factory()->count(40)->create([
        'answer_id' => $answer->id,
    ]);
    $topic->update(['accepted_answer_id' => $answer->id]);

    expect(KnowledgeArticle::query()
        ->where('source_topic_id', $topic->id)
        ->doesntExist())->toBeTrue();
});

test('guide creation records a maintainer immutable version and immutable workflow event', function () {
    $administrator = User::factory()->administrator()->create();

    $article = app(CreateKnowledgeGuide::class)->handle(
        $administrator,
        collaborativeGuideData(),
    );

    expect($article->status)->toBe(KnowledgeStatus::Draft)
        ->and($article->translation_group_key)->toStartWith('guide-')
        ->and($article->current_version)->toBe(1)
        ->and($article->lock_version)->toBe(0)
        ->and($article->activeCollaborators)->toHaveCount(1)
        ->and($article->activeCollaborators->first()?->role)
        ->toBe(KnowledgeCollaboratorRole::Maintainer)
        ->and($article->versions)->toHaveCount(1)
        ->and($article->workflowEvents)->toHaveCount(1)
        ->and($article->workflowEvents->first()?->event_type)
        ->toBe(KnowledgeWorkflowEventType::Created);

    $version = $article->versions->firstOrFail();
    $event = $article->workflowEvents->firstOrFail();

    expect(function () use ($version): void {
        $version->title = 'Silently rewritten history';
        $version->save();
    })->toThrow(LogicException::class);

    expect(function () use ($event): void {
        $event->reason_code = 'silently-rewritten';
        $event->save();
    })->toThrow(LogicException::class);
});

test('guide workflow requires independent scoped community and verified expert reviewers', function () {
    $administrator = User::factory()->administrator()->create();
    $article = app(CreateKnowledgeGuide::class)->handle(
        $administrator,
        collaborativeGuideData(),
    );
    $transition = app(TransitionKnowledgeGuide::class);

    $article = $transition->handle(
        $administrator,
        $article,
        KnowledgeStatus::SubmittedForReview,
        'The draft is complete and ready for independent review.',
        $article->lock_version,
    );

    expect(fn () => $transition->handle(
        $administrator,
        $article,
        KnowledgeStatus::CommunityReviewed,
        'An administrator cannot impersonate an independent community reviewer.',
        $article->lock_version,
    ))->toThrow(AuthorizationException::class);

    $communityReviewer = User::factory()->create();
    ForumUserTrustLevel::factory()->create([
        'user_id' => $communityReviewer->id,
        'forum_trust_level_id' => ForumTrustLevel::query()
            ->where('stable_key', 'community-reviewer')
            ->valueOrFail('id'),
    ]);
    KnowledgeArticleCollaborator::factory()->communityReviewer()->create([
        'article_id' => $article->id,
        'user_id' => $communityReviewer->id,
        'added_by_user_id' => $administrator->id,
    ]);

    $article = $transition->handle(
        $communityReviewer,
        $article,
        KnowledgeStatus::CommunityReviewed,
        'The community review checked clarity, sources, and practical safety.',
        $article->lock_version,
    );

    $expertReviewer = User::factory()->create();
    KnowledgeArticleCollaborator::factory()->expertReviewer()->create([
        'article_id' => $article->id,
        'user_id' => $expertReviewer->id,
        'added_by_user_id' => $administrator->id,
    ]);

    expect(fn () => $transition->handle(
        $expertReviewer,
        $article,
        KnowledgeStatus::ExpertReviewed,
        'A role label alone must not create verified professional authority.',
        $article->lock_version,
    ))->toThrow(AuthorizationException::class);

    ExpertProfile::factory()->create([
        'owner_id' => $expertReviewer->id,
        'owner_key' => $expertReviewer->actor_key,
        'verification_status' => VerificationStatus::Verified,
        'verification_expires_at' => now()->addMonth(),
    ]);

    $article = $transition->handle(
        $expertReviewer,
        $article,
        KnowledgeStatus::ExpertReviewed,
        'The verified reviewer checked the guide within their professional scope.',
        $article->lock_version,
    );
    $article = $transition->handle(
        $administrator,
        $article,
        KnowledgeStatus::Published,
        'All required editorial reviews are complete and documented.',
        $article->lock_version,
    );

    expect($article->status)->toBe(KnowledgeStatus::Published)
        ->and($article->published_at)->not->toBeNull()
        ->and($article->last_reviewed_at)->not->toBeNull()
        ->and($article->next_review_at)->not->toBeNull()
        ->and($article->workflowEvents()->count())->toBe(5);
});

test('guide revisions use optimistic locking and preserve every snapshot', function () {
    $administrator = User::factory()->administrator()->create();
    $article = app(CreateKnowledgeGuide::class)->handle(
        $administrator,
        collaborativeGuideData(),
    );
    $save = app(SaveKnowledgeGuideRevision::class);

    $article = $save->handle(
        $administrator,
        $article,
        collaborativeGuideData([
            'title' => 'Revised safe introductions guide',
            'changeSummary' => 'Clarified the staged introduction sequence.',
            'expectedLockVersion' => 0,
        ]),
    );

    expect($article->current_version)->toBe(2)
        ->and($article->lock_version)->toBe(1)
        ->and($article->versions()->count())->toBe(2)
        ->and($article->versions()->latest('version_number')->first()?->title)
        ->toBe('Revised safe introductions guide');

    expect(fn () => $save->handle(
        $administrator,
        $article,
        collaborativeGuideData([
            'title' => 'Conflicting stale revision',
            'changeSummary' => 'This request started from an obsolete browser snapshot.',
            'expectedLockVersion' => 0,
        ]),
    ))->toThrow(ValidationException::class);

    expect($article->refresh()->title)->toBe('Revised safe introductions guide')
        ->and($article->versions()->count())->toBe(2);
});

test('collaborator grants are idempotent and the final maintainer cannot be revoked', function () {
    $administrator = User::factory()->administrator()->create();
    $article = app(CreateKnowledgeGuide::class)->handle(
        $administrator,
        collaborativeGuideData(),
    );
    $contributor = User::factory()->create();
    $action = app(ManageKnowledgeCollaborator::class);

    $first = $action->grant(
        $administrator,
        $article,
        $contributor,
        KnowledgeCollaboratorRole::Contributor,
    );
    $same = $action->grant(
        $administrator,
        $article,
        $contributor,
        KnowledgeCollaboratorRole::Contributor,
    );

    expect($same->id)->toBe($first->id)
        ->and(KnowledgeArticleCollaborator::query()
            ->where('article_id', $article->id)
            ->where('user_id', $contributor->id)
            ->count())->toBe(1)
        ->and($article->workflowEvents()
            ->where('event_type', KnowledgeWorkflowEventType::CollaboratorAdded->value)
            ->count())->toBe(1);

    $maintainer = $article->activeCollaborators()
        ->where('role', KnowledgeCollaboratorRole::Maintainer->value)
        ->firstOrFail();

    expect(fn () => $action->revoke($administrator, $maintainer))
        ->toThrow(ValidationException::class);
});

test('editorial locks prevent collaborator changes and preserve an auditable reason', function () {
    $administrator = User::factory()->administrator()->create();
    $article = app(CreateKnowledgeGuide::class)->handle(
        $administrator,
        collaborativeGuideData(),
    );
    $contributor = User::factory()->create();
    app(ManageKnowledgeCollaborator::class)->grant(
        $administrator,
        $article,
        $contributor,
        KnowledgeCollaboratorRole::Contributor,
    );

    $article = app(SetKnowledgeEditorialLock::class)->handle(
        $administrator,
        $article,
        true,
        'Publication is paused while a safety concern is reviewed.',
    );

    expect($article->editorial_locked_at)->not->toBeNull()
        ->and($article->editorial_lock_reason)
        ->toBe('Publication is paused while a safety concern is reviewed.');

    expect(fn () => app(SaveKnowledgeGuideRevision::class)->handle(
        $contributor,
        $article,
        collaborativeGuideData([
            'changeSummary' => 'A collaborator attempted a revision during editorial lock.',
            'expectedLockVersion' => $article->lock_version,
        ]),
    ))->toThrow(ValidationException::class);

    $article = app(SetKnowledgeEditorialLock::class)->handle(
        $administrator,
        $article,
        false,
        null,
    );

    expect($article->editorial_locked_at)->toBeNull()
        ->and($article->workflowEvents()
            ->whereIn('event_type', [
                KnowledgeWorkflowEventType::EditorialLocked->value,
                KnowledgeWorkflowEventType::EditorialUnlocked->value,
            ])
            ->count())->toBe(2);
});

test('rollback creates a new version instead of rewriting old history', function () {
    $administrator = User::factory()->administrator()->create();
    $article = app(CreateKnowledgeGuide::class)->handle(
        $administrator,
        collaborativeGuideData(['title' => 'Original guide title']),
    );
    $firstVersion = $article->versions()->firstOrFail();
    $article = app(SaveKnowledgeGuideRevision::class)->handle(
        $administrator,
        $article,
        collaborativeGuideData([
            'title' => 'Second guide title',
            'changeSummary' => 'Expanded the practical preparation section.',
            'expectedLockVersion' => $article->lock_version,
        ]),
    );

    $article = app(RollbackKnowledgeGuideVersion::class)->handle(
        $administrator,
        $article,
        $firstVersion,
        'Restore the safer original wording while the revision is reviewed.',
        $article->lock_version,
    );

    expect($article->title)->toBe('Original guide title')
        ->and($article->current_version)->toBe(3)
        ->and($article->versions()->count())->toBe(3)
        ->and($article->workflowEvents()
            ->where('event_type', KnowledgeWorkflowEventType::RolledBack->value)
            ->count())->toBe(1);
});

test('accepted corrections move public guidance into correction review with exact history', function () {
    $administrator = User::factory()->administrator()->create();
    $article = KnowledgeArticle::factory()->create([
        'created_by_user_id' => $administrator->id,
        'status' => KnowledgeStatus::Published,
    ]);
    $correction = KnowledgeCorrection::factory()->create([
        'article_id' => $article->id,
        'reporter_user_id' => $this->authenticatedUser->id,
    ]);

    $reviewed = app(ReviewKnowledgeCorrection::class)->handle(
        $administrator,
        $correction,
        KnowledgeCorrectionStatus::Accepted,
        'The cited source confirms that this section needs revision.',
    );
    $event = $article->workflowEvents()->latest('id')->firstOrFail();

    expect($reviewed->status)->toBe(KnowledgeCorrectionStatus::Accepted)
        ->and($reviewed->reviewed_by_user_id)->toBe($administrator->id)
        ->and($article->refresh()->status)->toBe(KnowledgeStatus::CorrectionRequested)
        ->and($event->from_status)->toBe(KnowledgeStatus::Published)
        ->and($event->to_status)->toBe(KnowledgeStatus::CorrectionRequested);
});

test('public exports are printable and draft exports remain private', function () {
    $published = KnowledgeArticle::factory()->create([
        'title' => 'Public printable welfare guide',
        'body' => 'This is the complete reviewed body for a public printable welfare guide.',
        'sources' => ['https://example.test/source'],
    ]);
    $draft = KnowledgeArticle::factory()->draft()->create();

    $this->get(route('knowledge.articles.export', $published))
        ->assertOk()
        ->assertHeader('content-type', 'text/markdown; charset=UTF-8')
        ->assertHeader('x-content-type-options', 'nosniff')
        ->assertSee('# Public printable welfare guide')
        ->assertSee('https://example.test/source');
    $this->get(route('knowledge.articles.print', $published))
        ->assertOk()
        ->assertSee('Public printable welfare guide');
    $this->get(route('knowledge.articles.export', $draft))->assertForbidden();
    $this->get(route('knowledge.articles.print', $draft))->assertForbidden();
});

test('translation groups permit one guide per locale and preserve the original locale', function () {
    $english = KnowledgeArticle::factory()->create([
        'translation_group_key' => 'guide-animal-welfare-basics',
        'language' => 'en',
        'title' => 'Animal welfare basics',
    ]);
    $lithuanian = KnowledgeArticle::factory()->create([
        'translation_group_key' => $english->translation_group_key,
        'language' => 'lt',
        'title' => 'Gyvūnų gerovės pagrindai',
    ]);

    expect($english->id)->not->toBe($lithuanian->id)
        ->and($english->language)->toBe('en')
        ->and($lithuanian->language)->toBe('lt');

    expect(fn () => KnowledgeArticle::factory()->create([
        'translation_group_key' => $english->translation_group_key,
        'language' => 'en',
    ]))->toThrow(QueryException::class);
});

test('public guide pages expose only public translations from the same stable group', function () {
    $english = KnowledgeArticle::factory()->create([
        'title' => 'Accessible home guide',
        'translation_group_key' => 'guide-accessible-home',
        'language' => 'en',
    ]);
    $lithuanian = KnowledgeArticle::factory()->create([
        'title' => 'Prieinamu namu vadovas',
        'translation_group_key' => $english->translation_group_key,
        'language' => 'lt',
    ]);
    $russianDraft = KnowledgeArticle::factory()->draft()->create([
        'title' => 'Черновик перевода',
        'translation_group_key' => $english->translation_group_key,
        'language' => 'ru',
    ]);
    KnowledgeArticle::factory()->create([
        'title' => 'Unrelated guide',
        'translation_group_key' => 'guide-unrelated',
        'language' => 'ru',
    ]);

    $presentedArticle = app(KnowledgePresenter::class)->article($english)['article'];

    expect(array_column($presentedArticle['translations'], 'slug'))
        ->toBe([$lithuanian->slug]);

    $this->get(route('knowledge.articles.show', $english))
        ->assertOk()
        ->assertSee('Other languages')
        ->assertSee($lithuanian->title)
        ->assertSee(route('knowledge.articles.show', $lithuanian))
        ->assertDontSee($russianDraft->title);
});

test('livewire editor creates validated guides and protects server-owned identity', function () {
    $administrator = User::factory()->administrator()->create();

    $this->actingAs($administrator)
        ->get(route('knowledge.guides.create'))
        ->assertOk();
    $component = Livewire::actingAs($administrator)
        ->test(KnowledgeGuideEditor::class);
    $component
        ->set('form.title', 'Accessible guide for safe animal introductions')
        ->set('form.summary', 'A practical, staged guide for introducing animals without avoidable stress.')
        ->set('form.body', str_repeat(
            'Observe body language, provide separation, and progress only at a comfortable pace. ',
            3,
        ))
        ->set('form.category', 'behavior')
        ->set('form.type', 'guide')
        ->set('form.difficulty', 'beginner')
        ->set('form.language', 'en')
        ->set('form.sourcesText', 'https://example.test/animal-introductions')
        ->set('form.changeSummary', 'Created the first complete editorial draft.')
        ->call('save')
        ->assertHasNoErrors();

    $article = KnowledgeArticle::query()
        ->where('title', 'Accessible guide for safe animal introductions')
        ->firstOrFail();

    $component->assertRedirect(route('knowledge.guides.edit', $article));

    $other = KnowledgeArticle::factory()->draft()->create();
    $editor = Livewire::actingAs($administrator)
        ->test(KnowledgeGuideEditor::class, ['articleId' => $article->id])
        ->assertSet('articleId', $article->id)
        ->assertSet('articleLockVersion', $article->lock_version)
        ->assertSet('form.title', $article->title);

    expect(fn () => $editor->set('articleId', $other->id))
        ->toThrow(CannotUpdateLockedPropertyException::class);
});

test('administrators can discover every guide state from the bounded guide registry', function () {
    $administrator = User::factory()->administrator()->create();
    $article = KnowledgeArticle::factory()->submittedForReview()->create([
        'title' => 'Guide awaiting independent review',
    ]);
    KnowledgeArticleCollaborator::factory()->maintainer()->create([
        'article_id' => $article->id,
        'user_id' => $administrator->id,
    ]);
    KnowledgeCorrection::factory()->create([
        'article_id' => $article->id,
    ]);

    Livewire::actingAs($administrator)
        ->test(AdminDashboard::class)
        ->set('tab', 'guides')
        ->assertSet('tab', 'guides')
        ->assertSee('Collaborative guide registry')
        ->assertSee('Guide awaiting independent review')
        ->assertSee(KnowledgeStatus::SubmittedForReview->label())
        ->assertSee('Create guide')
        ->set('search', 'does-not-exist')
        ->assertSee('No guides match this search.')
        ->assertDontSee('Guide awaiting independent review');
});

/**
 * @param  array<string, mixed>  $overrides
 */
function collaborativeGuideData(array $overrides = []): KnowledgeGuideData
{
    return new KnowledgeGuideData(
        title: (string) ($overrides['title'] ?? 'Safe animal introductions guide'),
        summary: (string) ($overrides['summary']
            ?? 'A practical guide for gradual, low-stress introductions between household animals.'),
        body: (string) ($overrides['body'] ?? str_repeat(
            'Prepare separate resources, observe body language, and increase contact gradually. ',
            3,
        )),
        category: (string) ($overrides['category'] ?? 'behavior'),
        type: (string) ($overrides['type'] ?? 'guide'),
        difficulty: (string) ($overrides['difficulty'] ?? 'beginner'),
        audience: $overrides['audience'] ?? 'Animal owners',
        language: (string) ($overrides['language'] ?? 'en'),
        jurisdiction: $overrides['jurisdiction'] ?? null,
        taxonId: $overrides['taxonId'] ?? null,
        discussionTopicId: $overrides['discussionTopicId'] ?? null,
        sources: $overrides['sources'] ?? ['https://example.test/animal-welfare'],
        protectedSections: $overrides['protectedSections'] ?? ['Safety boundary'],
        changeSummary: (string) ($overrides['changeSummary']
            ?? 'Created the initial complete guide draft.'),
        expectedLockVersion: (int) ($overrides['expectedLockVersion'] ?? 0),
    );
}
