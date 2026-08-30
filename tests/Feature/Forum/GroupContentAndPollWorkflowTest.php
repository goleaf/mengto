<?php

declare(strict_types=1);

use App\Actions\AssociateForumTopicWithGroup;
use App\Actions\AssociateKnowledgeGuideWithGroup;
use App\Actions\CastForumPollVote;
use App\Actions\CreateForumGroupActivity;
use App\Actions\CreateForumPoll;
use App\Actions\PrepareForumGroupFileDownload;
use App\Actions\PublishForumGroupAnnouncement;
use App\Actions\StoreForumGroupFile;
use App\Data\CastForumPollVoteData;
use App\Data\CreateForumGroupActivityData;
use App\Data\CreateForumGroupAnnouncementData;
use App\Data\CreateForumPollData;
use App\Enums\ForumGroupActivityFormat;
use App\Enums\ForumGroupMembershipState;
use App\Enums\ForumGroupRole;
use App\Enums\ForumPollEligibility;
use App\Enums\ForumPollResultVisibility;
use App\Enums\ForumPollStatus;
use App\Enums\ForumPollType;
use App\Enums\ForumPollVoterVisibility;
use App\Enums\ForumVisibility;
use App\Livewire\Forum\GroupContentWorkspace;
use App\Models\ForumGroup;
use App\Models\ForumGroupActivity;
use App\Models\ForumGroupAnnouncement;
use App\Models\ForumGroupMembership;
use App\Models\ForumPoll;
use App\Models\ForumPollVote;
use App\Models\ForumTopic;
use App\Models\ForumTrustLevel;
use App\Models\ForumUserTrustLevel;
use App\Models\KnowledgeArticle;
use App\Models\KnowledgeArticleCollaborator;
use App\Models\User;
use App\Services\SocialActorResolver;
use Carbon\CarbonImmutable;
use Database\Seeders\ForumReputationDefinitionSeeder;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function activeGroupMember(ForumGroup $group, ?User $user = null): User
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

/**
 * @param  list<string>  $options
 */
function pollData(
    array $options = ['Morning', 'Evening'],
    ForumPollType $type = ForumPollType::SingleChoice,
    ForumPollVoterVisibility $voterVisibility = ForumPollVoterVisibility::Anonymous,
    ForumPollResultVisibility $resultVisibility = ForumPollResultVisibility::AfterVote,
    bool $editable = true,
    ForumPollEligibility $eligibility = ForumPollEligibility::GroupMembers,
    ?CarbonImmutable $closesAt = null,
    string $token = 'poll:create:one',
): CreateForumPollData {
    return new CreateForumPollData(
        question: 'Which option should the group choose?',
        description: 'A bounded community preference poll.',
        options: $options,
        type: $type,
        voterVisibility: $voterVisibility,
        resultVisibility: $resultVisibility,
        isVoteEditable: $editable,
        eligibility: $eligibility,
        closesAt: $closesAt ?? now()->addWeek()->toImmutable(),
        idempotencyKey: $token,
    );
}

test('topics and guides can be associated without changing their identifiers', function () {
    $owner = User::factory()->create();
    $group = ForumGroup::factory()->for($owner, 'owner')->create();
    $topic = ForumTopic::factory()->create([
        'author_id' => $owner->id,
        'author_key' => $owner->actor_key,
    ]);
    $guide = KnowledgeArticle::factory()->create(['created_by_user_id' => $owner->id]);
    KnowledgeArticleCollaborator::factory()
        ->maintainer()
        ->for($guide, 'article')
        ->for($owner, 'user')
        ->create();
    $topicId = $topic->id;
    $guideId = $guide->id;

    app(AssociateForumTopicWithGroup::class)->handle($owner, $group, $topic);
    app(AssociateKnowledgeGuideWithGroup::class)->handle($owner, $group, $guide);

    expect($topic->refresh())
        ->id->toBe($topicId)
        ->forum_group_id->toBe($group->id)
        ->visibility->toBe(ForumVisibility::Group)
        ->and($guide->refresh())
        ->id->toBe($guideId)
        ->forum_group_id->toBe($group->id);
});

test('a member cannot associate another authors topic or guide', function () {
    $owner = User::factory()->create();
    $member = activeGroupMember($group = ForumGroup::factory()->for($owner, 'owner')->create());
    $topic = ForumTopic::factory()->create();

    app(AssociateForumTopicWithGroup::class)->handle($member, $group, $topic);
})->throws(AuthorizationException::class);

test('group event and announcement creation are validated and idempotent', function () {
    $owner = User::factory()->create();
    $group = ForumGroup::factory()->for($owner, 'owner')->create();
    $activityData = new CreateForumGroupActivityData(
        title: 'Accessible park walk',
        summary: 'A calm group walk with a public meeting point.',
        format: ForumGroupActivityFormat::Physical,
        startsAt: now()->addWeek()->toImmutable(),
        endsAt: now()->addWeek()->addHours(2)->toImmutable(),
        timezone: 'Europe/Vilnius',
        locationScope: 'lt-vilnius',
        onlineUrl: null,
        capacity: 20,
        participationNotes: 'Bring individual water.',
        idempotencyKey: 'activity:create:one',
    );
    $announcementData = new CreateForumGroupAnnouncementData(
        title: 'Updated meeting guidance',
        body: 'Use the public entrance and keep private addresses out of replies.',
        publishedAt: now()->toImmutable(),
        expiresAt: now()->addMonth()->toImmutable(),
        idempotencyKey: 'announcement:create:one',
    );

    $activity = app(CreateForumGroupActivity::class)
        ->handle($owner, $group, $activityData);
    $sameActivity = app(CreateForumGroupActivity::class)
        ->handle($owner, $group, $activityData);
    $announcement = app(PublishForumGroupAnnouncement::class)
        ->handle($owner, $group, $announcementData);
    $sameAnnouncement = app(PublishForumGroupAnnouncement::class)
        ->handle($owner, $group, $announcementData);

    expect($sameActivity->is($activity))->toBeTrue()
        ->and($sameAnnouncement->is($announcement))->toBeTrue()
        ->and(ForumGroupActivity::query()->count())->toBe(1)
        ->and(ForumGroupAnnouncement::query()->count())->toBe(1)
        ->and($activity->forum_event_id)->not->toBeNull()
        ->and($activity->event?->forum_group_id)->toBe($group->id)
        ->and($activity->event?->type->value)->toBe('club_meetup');
});

test('group files remain private and require membership at download time', function () {
    Storage::fake('local');
    $owner = User::factory()->create();
    $group = ForumGroup::factory()->for($owner, 'owner')->create();
    $member = activeGroupMember($group);
    $outsider = User::factory()->create();
    $upload = UploadedFile::fake()->createWithContent(
        'care-checklist.txt',
        "Private group checklist\n",
    );

    $file = app(StoreForumGroupFile::class)->handle(
        $owner,
        $group,
        $upload,
        'Checklist for active members.',
        'file:upload:one',
    );

    Storage::disk('local')->assertExists($file->path);
    expect($file->path)->not->toContain('care-checklist')
        ->and($file->checksum)->toBe(hash('sha256', "Private group checklist\n"))
        ->and(Gate::forUser($member)->allows('view', $file))->toBeTrue()
        ->and(Gate::forUser($outsider)->allows('view', $file))->toBeFalse();

    $this->actingAs($member)
        ->get(route('forum.groups.files.download', [$group, $file]))
        ->assertOk()
        ->assertDownload('care-checklist.txt');

    $this->actingAs($outsider)
        ->get(route('forum.groups.files.download', [$group, $file]))
        ->assertForbidden();

    expect(fn () => app(PrepareForumGroupFileDownload::class)->handle($outsider, $group, $file))
        ->toThrow(AuthorizationException::class);
});

test('poll creation supports every choice mode and is idempotent', function (
    ForumPollType $type,
) {
    $owner = User::factory()->create();
    $group = ForumGroup::factory()->for($owner, 'owner')->create();
    $data = pollData(
        type: $type,
        token: 'poll:create:'.$type->value,
    );

    $poll = app(CreateForumPoll::class)->handle($owner, $group, $data);
    $samePoll = app(CreateForumPoll::class)->handle($owner, $group, $data);

    expect($samePoll->is($poll))->toBeTrue()
        ->and($poll->options)->toHaveCount(2)
        ->and($poll->type)->toBe($type)
        ->and(ForumPoll::query()->count())->toBe(1);
})->with(ForumPollType::cases());

test('single and multiple choice votes update bounded counters consistently', function () {
    $owner = User::factory()->create();
    $group = ForumGroup::factory()->for($owner, 'owner')->create();
    $member = activeGroupMember($group);
    $create = app(CreateForumPoll::class);
    $cast = app(CastForumPollVote::class);
    $single = $create->handle($owner, $group, pollData(token: 'poll:single'));
    $multiple = $create->handle(
        $owner,
        $group,
        pollData(
            options: ['One', 'Two', 'Three'],
            type: ForumPollType::MultipleChoice,
            token: 'poll:multiple',
        ),
    );

    $singleVote = $cast->handle(
        $member,
        $single,
        new CastForumPollVoteData(
            [$single->options[0]->id],
            'vote:single:first',
        ),
    );
    $cast->handle(
        $member,
        $multiple,
        new CastForumPollVoteData(
            [$multiple->options[0]->id, $multiple->options[2]->id],
            'vote:multiple:first',
        ),
    );

    expect($singleVote->choices)->toBe([$single->options[0]->id])
        ->and($single->refresh()->total_vote_count)->toBe(1)
        ->and($single->options()->orderBy('position')->pluck('selection_count')->all())
        ->toBe([1, 0])
        ->and($multiple->refresh()->total_vote_count)->toBe(1)
        ->and($multiple->options()->orderBy('position')->pluck('selection_count')->all())
        ->toBe([1, 0, 1]);
});

test('ranked votes preserve order and editable votes replace counters', function () {
    $owner = User::factory()->create();
    $group = ForumGroup::factory()->for($owner, 'owner')->create();
    $member = activeGroupMember($group);
    $poll = app(CreateForumPoll::class)->handle(
        $owner,
        $group,
        pollData(
            options: ['One', 'Two', 'Three'],
            type: ForumPollType::RankedChoice,
            token: 'poll:ranked',
        ),
    );
    $options = $poll->options;
    $cast = app(CastForumPollVote::class);
    $vote = $cast->handle(
        $member,
        $poll,
        new CastForumPollVoteData(
            [$options[2]->id, $options[0]->id, $options[1]->id],
            'vote:ranked:first',
        ),
    );
    $updated = $cast->handle(
        $member,
        $poll->refresh(),
        new CastForumPollVoteData(
            [$options[0]->id, $options[1]->id, $options[2]->id],
            'vote:ranked:second',
            $vote->lock_version,
        ),
    );

    expect($updated->choices)->toBe([
        $options[0]->id,
        $options[1]->id,
        $options[2]->id,
    ])->and($poll->refresh()->total_vote_count)->toBe(1)
        ->and($poll->options()->orderBy('position')->pluck('selection_count')->all())
        ->toBe([1, 1, 1])
        ->and($poll->options()->orderBy('position')->pluck('first_choice_count')->all())
        ->toBe([1, 0, 0]);
});

test('non editable polls reject a second vote and keep one database row', function () {
    $owner = User::factory()->create();
    $group = ForumGroup::factory()->for($owner, 'owner')->create();
    $member = activeGroupMember($group);
    $poll = app(CreateForumPoll::class)->handle(
        $owner,
        $group,
        pollData(editable: false, token: 'poll:final'),
    );
    $cast = app(CastForumPollVote::class);
    $cast->handle(
        $member,
        $poll,
        new CastForumPollVoteData([$poll->options[0]->id], 'vote:final:first'),
    );

    expect(fn () => $cast->handle(
        $member,
        $poll->refresh(),
        new CastForumPollVoteData([$poll->options[1]->id], 'vote:final:second'),
    ))->toThrow(ValidationException::class)
        ->and(ForumPollVote::query()->count())->toBe(1);
});

test('poll closure is derived from closes at without scheduler state', function () {
    $owner = User::factory()->create();
    $group = ForumGroup::factory()->for($owner, 'owner')->create();
    $member = activeGroupMember($group);
    $poll = ForumPoll::factory()
        ->for($group, 'group')
        ->for($owner, 'creator')
        ->closed()
        ->create();

    expect($poll->status)->toBe(ForumPollStatus::Active)
        ->and($poll->isClosed())->toBeTrue()
        ->and(fn () => app(CastForumPollVote::class)->handle(
            $member,
            $poll,
            new CastForumPollVoteData([$poll->options[0]->id], 'vote:closed'),
        ))->toThrow(ValidationException::class);
});

test('result and voter visibility rules are enforced independently', function () {
    $owner = User::factory()->create();
    $group = ForumGroup::factory()->for($owner, 'owner')->create();
    $member = activeGroupMember($group);
    $poll = app(CreateForumPoll::class)->handle(
        $owner,
        $group,
        pollData(
            voterVisibility: ForumPollVoterVisibility::Visible,
            resultVisibility: ForumPollResultVisibility::AfterVote,
            token: 'poll:visible',
        ),
    );

    expect(Gate::forUser($member)->allows('viewResults', $poll))->toBeFalse()
        ->and(Gate::forUser($member)->allows('viewVoters', $poll))->toBeFalse();

    app(CastForumPollVote::class)->handle(
        $member,
        $poll,
        new CastForumPollVoteData([$poll->options[0]->id], 'vote:visible'),
    );

    expect(Gate::forUser($member)->allows('viewResults', $poll->refresh()))->toBeTrue()
        ->and(Gate::forUser($member)->allows('viewVoters', $poll))->toBeTrue();
});

test('trusted and location eligibility use explicit existing boundaries', function () {
    $this->seed(ForumReputationDefinitionSeeder::class);
    $owner = User::factory()->create();
    $group = ForumGroup::factory()
        ->for($owner, 'owner')
        ->create(['location_scope' => 'lt-vilnius']);
    $member = activeGroupMember($group);
    $trustedPoll = app(CreateForumPoll::class)->handle(
        $owner,
        $group,
        pollData(
            eligibility: ForumPollEligibility::TrustedMembers,
            token: 'poll:trusted',
        ),
    );
    $locationPoll = app(CreateForumPoll::class)->handle(
        $owner,
        $group,
        pollData(
            eligibility: ForumPollEligibility::LocationMembers,
            token: 'poll:location',
        ),
    );

    expect(fn () => app(CastForumPollVote::class)->handle(
        $member,
        $trustedPoll,
        new CastForumPollVoteData([$trustedPoll->options[0]->id], 'vote:untrusted'),
    ))->toThrow(ValidationException::class);

    $level = ForumTrustLevel::query()
        ->where('stable_key', 'trusted-contributor')
        ->firstOrFail();
    ForumUserTrustLevel::query()->create([
        'user_id' => $member->id,
        'forum_trust_level_id' => $level->id,
        'scope_type' => 'global',
        'scope_key' => '*',
        'reason_code' => 'test-grant',
        'granted_at' => now(),
    ]);

    app(CastForumPollVote::class)->handle(
        $member,
        $trustedPoll,
        new CastForumPollVoteData([$trustedPoll->options[0]->id], 'vote:trusted'),
    );
    app(CastForumPollVote::class)->handle(
        $member,
        $locationPoll,
        new CastForumPollVoteData([$locationPoll->options[0]->id], 'vote:location'),
    );

    expect(ForumPollVote::query()->where('user_id', $member->id)->count())->toBe(2);
});

test('duplicate vote retries are idempotent and never create another row', function () {
    $owner = User::factory()->create();
    $group = ForumGroup::factory()->for($owner, 'owner')->create();
    $member = activeGroupMember($group);
    $poll = app(CreateForumPoll::class)->handle(
        $owner,
        $group,
        pollData(token: 'poll:idempotent'),
    );
    $data = new CastForumPollVoteData(
        [$poll->options[0]->id],
        'vote:idempotent',
    );

    $first = app(CastForumPollVote::class)->handle($member, $poll, $data);
    $second = app(CastForumPollVote::class)->handle($member, $poll, $data);

    expect($second->is($first))->toBeTrue()
        ->and(ForumPollVote::query()->count())->toBe(1)
        ->and($poll->refresh()->total_vote_count)->toBe(1);
});

test('group content livewire surface renders modes and records a vote', function () {
    $owner = User::factory()->create();
    $group = ForumGroup::factory()->for($owner, 'owner')->create();
    $poll = ForumPoll::factory()
        ->for($group, 'group')
        ->for($owner, 'creator')
        ->visibleVoters()
        ->publicResults()
        ->create();

    Livewire::actingAs($owner)
        ->test(GroupContentWorkspace::class, ['groupId' => $group->id])
        ->assertSee(__('forum_polls.notices.poll_authority'))
        ->assertSee($poll->question)
        ->set("pollChoices.{$poll->id}", $poll->options[0]->id)
        ->call('castVote', $poll->id)
        ->assertHasNoErrors()
        ->assertSee(__('forum_polls.feedback.vote_recorded'));

    expect(ForumPollVote::query()
        ->where('forum_poll_id', $poll->id)
        ->where('user_id', $owner->id)
        ->count())->toBe(1);
});

test('group content query count stays bounded as poll volume grows', function () {
    $owner = User::factory()->create();
    $group = ForumGroup::factory()->for($owner, 'owner')->create();
    ForumPoll::factory()
        ->for($group, 'group')
        ->for($owner, 'creator')
        ->visibleVoters()
        ->publicResults()
        ->create();

    $renderQueryCount = static function () use ($group, $owner): int {
        DB::flushQueryLog();
        DB::enableQueryLog();
        Livewire::actingAs($owner)
            ->test(GroupContentWorkspace::class, ['groupId' => $group->id])
            ->assertOk();
        $queryCount = count(DB::getQueryLog());
        DB::disableQueryLog();

        return $queryCount;
    };
    $singlePollQueries = $renderQueryCount();

    ForumPoll::factory()
        ->count(9)
        ->for($group, 'group')
        ->for($owner, 'creator')
        ->visibleVoters()
        ->publicResults()
        ->create();
    $tenPollQueries = $renderQueryCount();

    expect($singlePollQueries)->toBeLessThanOrEqual(24)
        ->and($tenPollQueries)->toBeLessThanOrEqual($singlePollQueries + 1);
});

test('group content hydrates vote state only for polls visible in the workspace', function () {
    $owner = User::factory()->create();
    $group = ForumGroup::factory()->for($owner, 'owner')->create();
    $visiblePoll = ForumPoll::factory()
        ->for($group, 'group')
        ->for($owner, 'creator')
        ->create(['created_at' => now()]);
    ForumPollVote::factory()
        ->for($visiblePoll, 'poll')
        ->for($owner, 'user')
        ->create();

    $archivedPolls = ForumPoll::factory()
        ->count(25)
        ->for($group, 'group')
        ->for($owner, 'creator')
        ->create([
            'archived_at' => now()->subDay(),
            'created_at' => now()->subDay(),
        ]);

    foreach ($archivedPolls as $archivedPoll) {
        ForumPollVote::factory()
            ->for($archivedPoll, 'poll')
            ->for($owner, 'user')
            ->create();
    }

    $component = Livewire::actingAs($owner)
        ->test(GroupContentWorkspace::class, ['groupId' => $group->id])
        ->assertOk();

    expect($component->get('pollChoices'))
        ->toHaveCount(1)
        ->toHaveKey($visiblePoll->id)
        ->and($component->get('voteVersions'))
        ->toHaveCount(1)
        ->toHaveKey($visiblePoll->id)
        ->and($component->get('voteTokens'))
        ->toHaveCount(1)
        ->toHaveKey($visiblePoll->id);
});

test('private group content component rejects a non member directly', function () {
    $owner = User::factory()->create();
    $group = ForumGroup::factory()->for($owner, 'owner')->private()->create();
    $outsider = User::factory()->create();

    Livewire::actingAs($outsider)
        ->test(GroupContentWorkspace::class, ['groupId' => $group->id])
        ->assertForbidden();
});

test('group topics and guides never leak through public directories', function () {
    $owner = User::factory()->create();
    $group = ForumGroup::factory()->for($owner, 'owner')->private()->create();
    $topic = ForumTopic::factory()->forGroup($group)->create([
        'title' => 'Private group topic sentinel',
    ]);
    $guide = KnowledgeArticle::factory()->forGroup($group)->create([
        'title' => 'Private group guide sentinel',
    ]);

    $this->get(route('forum.index'))
        ->assertOk()
        ->assertDontSee($topic->title);
    $this->get(route('knowledge.index'))
        ->assertOk()
        ->assertDontSee($guide->title);
    $this->get(route('forum.topics.show', $topic))->assertForbidden();
    $this->get(route('knowledge.articles.show', $guide))->assertNotFound();
});
