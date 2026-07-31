<?php

declare(strict_types=1);

use App\Actions\ArchiveForumJournal;
use App\Actions\BackfillForumJournals;
use App\Actions\CreateForumJournal;
use App\Actions\CreateForumJournalComment;
use App\Actions\CreateForumJournalEntry;
use App\Actions\GrantForumJournalCollaborator;
use App\Actions\PrepareForumJournalExport;
use App\Actions\RevokeForumJournalCollaborator;
use App\Actions\StoreForumJournalMedia;
use App\Actions\UpdateForumJournalEntry;
use App\Data\CreateForumJournalData;
use App\Data\CreateForumJournalEntryData;
use App\Data\UpdateForumJournalEntryData;
use App\Enums\ExpertProfileStatus;
use App\Enums\ForumGroupMembershipState;
use App\Enums\ForumGroupRole;
use App\Enums\ForumJournalCollaboratorRole;
use App\Enums\ForumJournalCollaboratorState;
use App\Enums\ForumJournalEntryKind;
use App\Enums\ForumJournalStatus;
use App\Enums\ForumJournalType;
use App\Enums\ForumTopicStatus;
use App\Enums\ForumTopicType;
use App\Enums\ForumVisibility;
use App\Enums\VerificationStatus;
use App\Livewire\Forum\ForumJournalDirectory;
use App\Livewire\Forum\ForumJournalTimeline;
use App\Models\AuditLog;
use App\Models\ExpertProfile;
use App\Models\ForumComment;
use App\Models\ForumGroup;
use App\Models\ForumGroupMembership;
use App\Models\ForumJournal;
use App\Models\ForumJournalCollaborator;
use App\Models\ForumJournalEntry;
use App\Models\ForumJournalEntryVersion;
use App\Models\ForumJournalMedia;
use App\Models\ForumTopic;
use App\Models\User;
use App\Services\SocialActorResolver;
use Carbon\CarbonImmutable;
use Database\Seeders\ForumCategorySeeder;
use Database\Seeders\ForumJournalBackfillSeeder;
use Database\Seeders\ForumJournalDemoSeeder;
use Database\Seeders\ForumSystemSeeder;
use Database\Seeders\ForumTopicTypeSeeder;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed([
        ForumCategorySeeder::class,
        ForumTopicTypeSeeder::class,
    ]);
});

function forumJournalData(
    string $idempotencyKey = 'journal-create-idempotency-0001',
    ForumJournalType $type = ForumJournalType::Training,
    ForumVisibility $visibility = ForumVisibility::Public,
): CreateForumJournalData {
    return new CreateForumJournalData(
        title: 'Scout training progress journal',
        body: 'A structured record of short training sessions, milestones, and setbacks.',
        categoryKey: 'training-education',
        type: $type,
        visibility: $visibility,
        startedOn: CarbonImmutable::today(),
        timezone: 'Europe/Vilnius',
        locale: 'en',
        idempotencyKey: $idempotencyKey,
    );
}

/**
 * @param  list<array{key: string, value: int|float|string}>  $measurements
 */
function forumJournalEntryData(
    string $idempotencyKey = 'journal-entry-idempotency-0001',
    array $measurements = [
        ['key' => 'duration_minutes', 'value' => 12],
        ['key' => 'success_percent', 'value' => 75],
    ],
): CreateForumJournalEntryData {
    return new CreateForumJournalEntryData(
        kind: ForumJournalEntryKind::Entry,
        title: 'Calm lead practice',
        body: 'Scout completed three short repetitions and remained relaxed.',
        occurredAt: CarbonImmutable::now()->subHour(),
        timezone: 'Europe/Vilnius',
        measurements: $measurements,
        idempotencyKey: $idempotencyKey,
    );
}

function forumJournalForUser(
    User $owner,
    ForumVisibility $visibility = ForumVisibility::Public,
    ForumJournalType $type = ForumJournalType::Training,
): ForumJournal {
    $journal = ForumJournal::factory()
        ->forUser($owner)
        ->withType($type)
        ->create();
    $journal->topic()->update([
        'visibility' => $visibility,
        'structured_data' => [
            'journal_type' => $type->value,
            'started_on' => now()->toDateString(),
        ],
    ]);

    return $journal->refresh()->load('topic');
}

function journalGroupMember(ForumGroup $group, ?User $user = null): User
{
    $user ??= User::factory()->create();
    $actor = app(SocialActorResolver::class)->forUser($user);

    ForumGroupMembership::query()->updateOrCreate(
        [
            'forum_group_id' => $group->id,
            'social_actor_id' => $actor->id,
        ],
        [
            'user_id' => $user->id,
            'role' => ForumGroupRole::Member,
            'state' => ForumGroupMembershipState::Active,
            'notification_level' => 'important',
            'accepted_rules_version' => $group->rules_version,
            'accepted_rules_at' => now(),
            'joined_at' => now(),
            'lock_version' => 0,
        ],
    );

    return $user;
}

test('journal schema provides lifecycle integrity and leading indexes', function () {
    expect(Schema::hasColumns('forum_journals', [
        'forum_topic_id',
        'owner_user_id',
        'stable_key',
        'creation_idempotency_key',
        'type',
        'status',
        'lock_version',
        'archived_at',
    ]))->toBeTrue()
        ->and(Schema::hasIndex(
            'forum_journals',
            'forum_journals_owner_status_updated_idx',
        ))->toBeTrue()
        ->and(Schema::hasIndex(
            'forum_journal_entries',
            'forum_journal_entries_journal_occurred_idx',
        ))->toBeTrue()
        ->and(Schema::hasIndex(
            'forum_journal_measurements',
            'forum_journal_measurements_entry_metric_unique',
        ))->toBeTrue()
        ->and(Schema::hasIndex(
            'forum_journal_collaborators',
            'forum_journal_collaborators_journal_user_unique',
        ))->toBeTrue()
        ->and(Schema::hasIndex(
            'forum_journal_media',
            'forum_journal_media_disk_path_unique',
        ))->toBeTrue();
});

test('every required journal type is represented and localized', function () {
    expect(array_column(ForumJournalType::cases(), 'value'))->toBe([
        'general',
        'training',
        'behavior',
        'recovery',
        'weight',
        'rehabilitation',
        'adoption-adaptation',
        'foster',
        'aquarium',
        'terrarium',
        'pregnancy-newborn',
        'senior-care',
    ]);

    foreach (['en', 'lt', 'ru'] as $locale) {
        app()->setLocale($locale);

        foreach (ForumJournalType::cases() as $type) {
            expect($type->label())->not->toBe("forum_journals.types.{$type->value}");
        }
    }
});

test('journal creation is validated transactional and idempotent', function () {
    $owner = User::factory()->create();
    $create = app(CreateForumJournal::class);

    $journal = $create->handle($owner, forumJournalData());
    $sameJournal = $create->handle($owner, forumJournalData());

    expect($sameJournal->is($journal))->toBeTrue()
        ->and(ForumJournal::query()->count())->toBe(1)
        ->and(ForumTopic::query()->count())->toBe(1)
        ->and($journal->owner_user_id)->toBe($owner->id)
        ->and($journal->topic->type)->toBe(ForumTopicType::Journal)
        ->and($journal->topic->status)->toBe(ForumTopicStatus::Published)
        ->and($journal->topic->structured_data)->toMatchArray([
            'journal_type' => ForumJournalType::Training->value,
        ])
        ->and(AuditLog::query()
            ->where('action', 'forum-journal.created')
            ->where('target_id', (string) $journal->id)
            ->exists())->toBeTrue();

    expect(fn () => $create->handle(
        User::factory()->create(),
        forumJournalData(),
    ))->toThrow(ValidationException::class)
        ->and(fn () => $create->handle(
            $owner,
            new CreateForumJournalData(
                title: 'Bad',
                body: 'Too short',
                categoryKey: 'missing-category',
                type: ForumJournalType::Training,
                visibility: ForumVisibility::Group,
                startedOn: CarbonImmutable::today(),
                timezone: 'Invalid/Timezone',
                locale: 'xx',
                idempotencyKey: 'journal-create-invalid-0001',
            ),
        ))->toThrow(ValidationException::class);
});

test('legacy journal backfill preserves topic identity and is repeatable', function () {
    $owner = User::factory()->create();
    $typed = ForumTopic::factory()->create([
        'author_id' => $owner->id,
        'author_key' => $owner->actor_key,
        'type' => ForumTopicType::Journal,
        'structured_data' => ['journal_type' => ForumJournalType::Aquarium->value],
    ]);
    $ambiguous = ForumTopic::factory()->create([
        'author_id' => $owner->id,
        'author_key' => $owner->actor_key,
        'type' => ForumTopicType::Journal,
        'structured_data' => [],
    ]);
    $ordinary = ForumTopic::factory()->create(['type' => ForumTopicType::Question]);

    $first = app(BackfillForumJournals::class)->handle();
    $second = app(BackfillForumJournals::class)->handle();

    expect($first)->toBe([
        'created' => 2,
        'unchanged' => 0,
        'review_required' => 1,
    ])->and($second)->toBe([
        'created' => 0,
        'unchanged' => 2,
        'review_required' => 0,
    ])->and(ForumJournal::query()->count())->toBe(2)
        ->and(ForumJournal::query()
            ->where('forum_topic_id', $typed->id)
            ->value('type'))->toBe(ForumJournalType::Aquarium)
        ->and(ForumJournal::query()
            ->where('forum_topic_id', $ambiguous->id)
            ->value('metadata'))->toMatchArray(['requires_type_review' => true])
        ->and(ForumJournal::query()
            ->where('forum_topic_id', $ordinary->id)
            ->exists())->toBeFalse();
});

test('journal visibility is enforced for guests members experts private users and groups', function () {
    $owner = User::factory()->create();
    $ordinary = User::factory()->create();
    $expert = User::factory()->create();
    ExpertProfile::factory()->create([
        'owner_id' => $expert->id,
        'owner_key' => $expert->actor_key,
        'status' => ExpertProfileStatus::Published,
        'verification_status' => VerificationStatus::Verified,
        'verification_expires_at' => now()->addMonth(),
    ]);
    $public = forumJournalForUser($owner);
    $link = forumJournalForUser($owner, ForumVisibility::Link);
    $members = forumJournalForUser($owner, ForumVisibility::Members);
    $experts = forumJournalForUser($owner, ForumVisibility::Experts);
    $private = forumJournalForUser($owner, ForumVisibility::Private);
    $viewer = User::factory()->create();
    ForumJournalCollaborator::factory()
        ->for($private, 'journal')
        ->for($viewer, 'user')
        ->create();

    expect(Gate::forUser(null)->allows('view', $public))->toBeTrue()
        ->and(Gate::forUser(null)->allows('view', $link))->toBeTrue()
        ->and(Gate::forUser(null)->allows('view', $members))->toBeFalse()
        ->and(Gate::forUser($ordinary)->allows('view', $members))->toBeTrue()
        ->and(Gate::forUser($ordinary)->allows('view', $experts))->toBeFalse()
        ->and(Gate::forUser($expert)->allows('view', $experts))->toBeTrue()
        ->and(Gate::forUser($owner)->allows('view', $private))->toBeTrue()
        ->and(Gate::forUser($viewer)->allows('view', $private))->toBeTrue()
        ->and(Gate::forUser($ordinary)->allows('view', $private))->toBeFalse();

    $group = ForumGroup::factory()->create();
    $member = journalGroupMember($group);
    $groupJournal = forumJournalForUser($owner, ForumVisibility::Group);
    $groupJournal->topic()->update([
        'forum_group_id' => $group->id,
        'visibility' => ForumVisibility::Group,
    ]);
    $groupJournal->unsetRelation('topic');

    expect(Gate::forUser($member)->allows('view', $groupJournal))->toBeTrue()
        ->and(Gate::forUser($ordinary)->allows('view', $groupJournal))->toBeFalse();
});

test('entries normalize measurements remain idempotent and reject invalid metrics', function () {
    $owner = User::factory()->create();
    $journal = forumJournalForUser($owner);
    $create = app(CreateForumJournalEntry::class);

    $entry = $create->handle($owner, $journal, forumJournalEntryData());
    $sameEntry = $create->handle($owner, $journal->refresh(), forumJournalEntryData());

    expect($sameEntry->is($entry))->toBeTrue()
        ->and(ForumJournalEntry::query()->count())->toBe(1)
        ->and($entry->measurements)->toHaveCount(2)
        ->and($entry->measurements->pluck('metric_key')->all())
        ->toBe(['duration_minutes', 'success_percent'])
        ->and($entry->measurements->pluck('unit')->all())
        ->toBe(['minutes', 'percent']);

    expect(fn () => $create->handle(
        $owner,
        $journal->refresh(),
        forumJournalEntryData(
            'journal-entry-invalid-key-0001',
            [['key' => 'pain_score', 'value' => 2]],
        ),
    ))->toThrow(ValidationException::class)
        ->and(fn () => $create->handle(
            $owner,
            $journal->refresh(),
            forumJournalEntryData(
                'journal-entry-invalid-range-01',
                [['key' => 'success_percent', 'value' => 101]],
            ),
        ))->toThrow(ValidationException::class);
});

test('entry edits use optimistic locking and preserve the previous version', function () {
    $owner = User::factory()->create();
    $journal = forumJournalForUser($owner);
    $entry = app(CreateForumJournalEntry::class)
        ->handle($owner, $journal, forumJournalEntryData());
    $update = app(UpdateForumJournalEntry::class);
    $data = new UpdateForumJournalEntryData(
        kind: ForumJournalEntryKind::Milestone,
        title: 'Calm practice milestone',
        body: 'Scout completed the full routine without pressure.',
        occurredAt: CarbonImmutable::now()->subMinutes(20),
        timezone: 'Europe/Vilnius',
        measurements: [
            ['key' => 'duration_minutes', 'value' => 15],
            ['key' => 'success_percent', 'value' => 100],
        ],
        expectedVersion: 0,
    );

    $updated = $update->handle($owner, $journal->refresh(), $entry, $data);
    $version = ForumJournalEntryVersion::query()->sole();

    expect($updated->kind)->toBe(ForumJournalEntryKind::Milestone)
        ->and($updated->lock_version)->toBe(1)
        ->and($version->version)->toBe(0)
        ->and($version->snapshot)->toMatchArray([
            'title' => 'Calm lead practice',
            'kind' => ForumJournalEntryKind::Entry->value,
        ])
        ->and($version->snapshot['measurements'])->toHaveCount(2);

    expect(fn () => $update->handle(
        $owner,
        $journal->refresh(),
        $updated,
        $data,
    ))->toThrow(ValidationException::class)
        ->and(ForumJournalEntryVersion::query()->count())->toBe(1);
});

test('collaborator roles are owner managed and revoked access stops immediately', function () {
    $owner = User::factory()->create();
    $viewer = User::factory()->create();
    $editor = User::factory()->create();
    $outsider = User::factory()->create();
    $journal = forumJournalForUser($owner, ForumVisibility::Private);
    $grant = app(GrantForumJournalCollaborator::class);

    $viewerGrant = $grant->handle(
        $owner,
        $journal,
        $viewer->email,
        ForumJournalCollaboratorRole::Viewer,
    );
    $editorGrant = $grant->handle(
        $owner,
        $journal->refresh(),
        $editor->email,
        ForumJournalCollaboratorRole::Editor,
    );

    expect(Gate::forUser($viewer)->allows('view', $journal->refresh()))->toBeTrue()
        ->and(Gate::forUser($viewer)->allows('update', $journal))->toBeFalse()
        ->and(Gate::forUser($editor)->allows('update', $journal))->toBeTrue()
        ->and(Gate::forUser($outsider)->allows('view', $journal))->toBeFalse()
        ->and(fn () => $grant->handle(
            $outsider,
            $journal,
            $outsider->email,
            ForumJournalCollaboratorRole::Viewer,
        ))->toThrow(AuthorizationException::class)
        ->and(fn () => $grant->handle(
            $owner,
            $journal,
            $owner->email,
            ForumJournalCollaboratorRole::Viewer,
        ))->toThrow(ValidationException::class);

    app(RevokeForumJournalCollaborator::class)
        ->handle($owner, $journal->refresh(), $editorGrant);

    expect($editorGrant->refresh()->state)->toBe(ForumJournalCollaboratorState::Revoked)
        ->and(Gate::forUser($editor)->allows('view', $journal->refresh()))->toBeFalse()
        ->and($viewerGrant->refresh()->state)->toBe(ForumJournalCollaboratorState::Active);
});

test('journal comments are authorized parent scoped moderated and idempotent', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $outsider = User::factory()->create();
    $journal = forumJournalForUser($owner, ForumVisibility::Members);
    $journal->topic()->update(['comment_policy' => 'review']);
    $entry = ForumJournalEntry::factory()->forJournal($journal)->by($owner)->create();
    $otherEntry = ForumJournalEntry::factory()->create();
    $create = app(CreateForumJournalComment::class);

    $comment = $create->handle(
        $member,
        $journal->refresh()->load('topic'),
        $entry,
        'A careful and useful progress update.',
        'journal-comment-idempotency-0001',
    );
    $sameComment = $create->handle(
        $member,
        $journal->refresh()->load('topic'),
        $entry,
        'A careful and useful progress update.',
        'journal-comment-idempotency-0001',
    );

    expect($sameComment->is($comment))->toBeTrue()
        ->and($comment->status)->toBe('review')
        ->and($comment->topic_id)->toBe($journal->forum_topic_id)
        ->and($comment->forum_journal_entry_id)->toBe($entry->id)
        ->and(ForumComment::query()->count())->toBe(1)
        ->and(fn () => $create->handle(
            $outsider,
            forumJournalForUser($owner, ForumVisibility::Private),
            $entry,
            'This must not be stored.',
            'journal-comment-denied-0000001',
        ))->toThrow(AuthorizationException::class)
        ->and(fn () => $create->handle(
            $member,
            $journal->refresh()->load('topic'),
            $otherEntry,
            'Wrong journal entry.',
            'journal-comment-parent-0000001',
        ))->toThrow(ModelNotFoundException::class);
});

test('journal media validates real images uses private generated paths and enforces nested access', function () {
    Storage::fake('local');
    $owner = User::factory()->create();
    $outsider = User::factory()->create();
    $journal = forumJournalForUser($owner, ForumVisibility::Private);
    $entry = ForumJournalEntry::factory()->forJournal($journal)->by($owner)->create();
    $upload = UploadedFile::fake()->image('private home address.jpg', 128, 128);
    $media = app(StoreForumJournalMedia::class)->handle(
        $owner,
        $journal,
        $entry,
        $upload,
        'Scout practicing calmly beside a mat',
        'Week one',
        'journal-media-idempotency-0001',
    );

    Storage::disk('local')->assertExists($media->path);
    expect($media->path)->not->toContain('private home address')
        ->and($media->original_name)->toBe('private home address.jpg')
        ->and($media->checksum)->toHaveLength(64)
        ->and($media->toArray())->not->toHaveKeys([
            'path',
            'original_name',
            'upload_idempotency_key',
        ]);

    $mediaResponse = $this->actingAs($owner)
        ->get(route('forum.journals.media.show', [$journal, $media]))
        ->assertOk()
        ->assertHeader('X-Content-Type-Options', 'nosniff');
    expect($mediaResponse->headers->get('Cache-Control'))
        ->toContain('private')
        ->toContain('no-store');
    $this->actingAs($outsider)
        ->get(route('forum.journals.media.show', [$journal, $media]))
        ->assertForbidden();

    $otherJournal = forumJournalForUser($owner);
    $this->actingAs($owner)
        ->get(route('forum.journals.media.show', [$otherJournal, $media]))
        ->assertNotFound();

    expect(fn () => app(StoreForumJournalMedia::class)->handle(
        $owner,
        $journal->refresh(),
        $entry,
        UploadedFile::fake()->createWithContent('fake.jpg', 'not an image'),
        '',
        null,
        'journal-media-invalid-0000001',
    ))->toThrow(ValidationException::class);
});

test('journal export is private bounded and omits storage and idempotency secrets', function () {
    Storage::fake('local');
    $owner = User::factory()->create();
    $viewer = User::factory()->create();
    $outsider = User::factory()->create();
    $journal = forumJournalForUser($owner, ForumVisibility::Private);
    $entry = app(CreateForumJournalEntry::class)
        ->handle($owner, $journal, forumJournalEntryData());
    app(CreateForumJournalComment::class)->handle(
        $owner,
        $journal->refresh()->load('topic'),
        $entry,
        'A private export comment.',
        'journal-export-comment-0000001',
    );
    $media = ForumJournalMedia::factory()
        ->for($entry, 'entry')
        ->for($owner, 'uploadedBy')
        ->create([
            'path' => 'forum-journals/private/server-only.jpg',
            'upload_idempotency_key' => 'journal-export-media-secret-001',
        ]);
    ForumJournalCollaborator::factory()
        ->for($journal, 'journal')
        ->for($viewer, 'user')
        ->create();

    $response = app(PrepareForumJournalExport::class)
        ->handle($viewer, $journal->refresh());
    ob_start();
    $response->sendContent();
    $content = (string) ob_get_clean();
    $payload = json_decode($content, true, flags: JSON_THROW_ON_ERROR);

    expect($payload['journal']['stable_key'])->toBe($journal->stable_key)
        ->and($payload['entries'])->toHaveCount(1)
        ->and($payload['entries'][0]['comments'])->toHaveCount(1)
        ->and($payload['entries'][0]['media'][0]['stable_key'])->toBe($media->stable_key)
        ->and($content)->not->toContain('server-only.jpg')
        ->and($content)->not->toContain('journal-export-media-secret-001')
        ->and($response->headers->get('Cache-Control'))->toContain('private')
        ->and(fn () => app(PrepareForumJournalExport::class)
            ->handle($outsider, $journal))
        ->toThrow(AuthorizationException::class);
});

test('journal http routes authenticate authorize and return protected responses', function () {
    $owner = $this->authenticatedUser;
    $outsider = User::factory()->create();
    $unverified = User::factory()->unverified()->create();
    $journal = forumJournalForUser($owner, ForumVisibility::Private);

    $this->get(route('forum.journals.index'))
        ->assertOk()
        ->assertSee(__('forum_journals.page.heading'));
    $topicResponse = $this->get(route('forum.topics.show', $journal->topic));
    $topicResponse->assertOk();

    expect(substr_count($topicResponse->getContent(), '<main'))->toBe(1);

    $this->get(route('forum.journals.export', $journal))
        ->assertOk()
        ->assertDownload();
    $this->actingAs($outsider)
        ->get(route('forum.journals.export', $journal))
        ->assertForbidden();
    $this->actingAs($unverified)
        ->get(route('forum.journals.index'))
        ->assertRedirect(route('verification.notice'));
});

test('archiving uses optimistic locking preserves history and disables mutations', function () {
    $owner = User::factory()->create();
    $journal = forumJournalForUser($owner);
    $entry = app(CreateForumJournalEntry::class)
        ->handle($owner, $journal, forumJournalEntryData());
    $comment = app(CreateForumJournalComment::class)->handle(
        $owner,
        $journal->refresh()->load('topic'),
        $entry,
        'This remains in the archived journal.',
        'journal-archive-comment-000001',
    );
    $currentVersion = $journal->refresh()->lock_version;

    expect(fn () => app(ArchiveForumJournal::class)->handle(
        $owner,
        $journal,
        $currentVersion - 1,
    ))->toThrow(ValidationException::class);

    $archived = app(ArchiveForumJournal::class)->handle(
        $owner,
        $journal->refresh(),
        $currentVersion,
    );

    expect($archived->status)->toBe(ForumJournalStatus::Archived)
        ->and($archived->topic->status)->toBe(ForumTopicStatus::Archived)
        ->and($archived->topic->is_locked)->toBeTrue()
        ->and(ForumJournalEntry::query()->whereKey($entry->id)->exists())->toBeTrue()
        ->and(ForumComment::query()->whereKey($comment->id)->exists())->toBeTrue()
        ->and(Gate::forUser($owner)->allows('update', $archived))->toBeFalse()
        ->and(Gate::forUser($owner)->allows('comment', $archived))->toBeFalse()
        ->and(Gate::forUser($owner)->allows('export', $archived))->toBeTrue();
});

test('livewire journal directory creates filters and protects its private scope', function () {
    $owner = $this->authenticatedUser;
    $mine = forumJournalForUser($owner);
    $other = forumJournalForUser(User::factory()->create(), ForumVisibility::Private);

    Livewire::test(ForumJournalDirectory::class)
        ->assertOk()
        ->assertSee($mine->topic->title)
        ->assertDontSee($other->topic->title)
        ->set('search', 'no matching journal title')
        ->assertSee(__('forum_journals.empty.journals_title'))
        ->set('search', '')
        ->set('form.title', 'Nori recovery progress journal')
        ->set('form.body', 'A structured record of appetite, comfort, and recovery observations.')
        ->set('form.categoryKey', 'health')
        ->set('form.type', ForumJournalType::Recovery->value)
        ->set('form.visibility', ForumVisibility::Private->value)
        ->call('create')
        ->assertHasNoErrors()
        ->assertRedirect();

    expect(ForumJournal::query()
        ->where('owner_user_id', $owner->id)
        ->where('type', ForumJournalType::Recovery->value)
        ->exists())->toBeTrue();
});

test('livewire timeline reauthorizes direct mutations and keeps locked ids immutable', function () {
    $owner = $this->authenticatedUser;
    $outsider = User::factory()->create();
    $journal = forumJournalForUser($owner, ForumVisibility::Private);
    $entry = ForumJournalEntry::factory()->forJournal($journal)->by($owner)->create();

    Livewire::actingAs($owner)
        ->test(ForumJournalTimeline::class, ['journalId' => $journal->id])
        ->assertOk()
        ->assertSee($entry->title)
        ->set('entryForm.title', 'Updated through Livewire')
        ->set('entryForm.body', 'The update remains authorized and validated on the server.')
        ->set('entryForm.occurredAt', now()->subMinute()->format('Y-m-d\TH:i'))
        ->set('entryForm.timezone', 'Europe/Vilnius')
        ->call('saveEntry')
        ->assertHasNoErrors()
        ->assertSee(__('forum_journals.feedback.entry_created'));

    Livewire::actingAs($outsider)
        ->test(ForumJournalTimeline::class, ['journalId' => $journal->id])
        ->assertForbidden();
});

test('livewire entry form validates future boundaries after timezone normalization', function () {
    $owner = User::factory()->create([
        'timezone' => 'Europe/Vilnius',
    ]);
    $journal = forumJournalForUser($owner);

    Livewire::actingAs($owner)
        ->test(ForumJournalTimeline::class, ['journalId' => $journal->id])
        ->set('entryForm.kind', ForumJournalEntryKind::Milestone->value)
        ->set('entryForm.title', 'Local-time milestone')
        ->set('entryForm.body', 'This local timestamp remains valid after conversion to an absolute instant.')
        ->set('entryForm.occurredAt', now('Europe/Vilnius')->format('Y-m-d\TH:i'))
        ->set('entryForm.metricValues.duration_minutes', '5')
        ->call('saveEntry')
        ->assertHasNoErrors()
        ->assertSee(__('forum_journals.feedback.entry_created'));

    expect($journal->entries()
        ->where('title', 'Local-time milestone')
        ->count())->toBe(1);
});

test('journal seeders are repeatable and demo data remains environment guarded', function () {
    $this->seed(ForumSystemSeeder::class);
    $owner = User::factory()->create();
    ForumTopic::factory()->create([
        'author_id' => $owner->id,
        'author_key' => $owner->actor_key,
        'type' => ForumTopicType::Journal,
        'structured_data' => ['journal_type' => ForumJournalType::Behavior->value],
    ]);

    $this->seed(ForumJournalBackfillSeeder::class);
    $count = ForumJournal::query()->count();
    $ids = ForumJournal::query()->orderBy('id')->pluck('id')->all();
    $this->seed(ForumJournalBackfillSeeder::class);

    expect(ForumJournal::query()->count())->toBe($count)
        ->and(ForumJournal::query()->orderBy('id')->pluck('id')->all())->toBe($ids);

    config()->set('platform.demo_seed_environments', []);

    expect(fn () => $this->seed(ForumJournalDemoSeeder::class))
        ->toThrow(LogicException::class);
});
