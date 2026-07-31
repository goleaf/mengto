<?php

declare(strict_types=1);

use App\Actions\ChangeForumGroupMemberRole;
use App\Actions\CreateForumGroup;
use App\Actions\InviteForumGroupMember;
use App\Actions\LeaveForumGroup;
use App\Actions\RequestForumGroupMembership;
use App\Actions\RespondToForumGroupInvitation;
use App\Actions\RestrictForumGroupMember;
use App\Actions\ReviewForumGroupMembership;
use App\Actions\SubmitForumReport;
use App\Actions\TransferForumGroupOwnership;
use App\Actions\TransitionForumGroup;
use App\Data\CreateForumGroupData;
use App\Data\ForumGroupInvitationData;
use App\Data\ForumGroupMembershipRequestData;
use App\Enums\ForumGroupEventType;
use App\Enums\ForumGroupInvitationState;
use App\Enums\ForumGroupMembershipState;
use App\Enums\ForumGroupRole;
use App\Enums\ForumGroupStatus;
use App\Enums\ForumGroupVisibility;
use App\Livewire\Forum\GroupDirectory;
use App\Livewire\Forum\GroupManagement;
use App\Livewire\Forum\GroupWorkspace;
use App\Models\ForumBlock;
use App\Models\ForumGroup;
use App\Models\ForumGroupActivity;
use App\Models\ForumGroupAnnouncement;
use App\Models\ForumGroupEvent;
use App\Models\ForumGroupFile;
use App\Models\ForumGroupInvitation;
use App\Models\ForumGroupMembership;
use App\Models\ForumPoll;
use App\Models\ForumReport;
use App\Models\TaxonVersion;
use App\Models\User;
use Carbon\CarbonImmutable;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\ForumGroupDefinitionSeeder;
use Database\Seeders\ForumGroupDemoSeeder;
use Database\Seeders\ForumSystemSeeder;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(ForumSystemSeeder::class);
});

function forumGroupCreateData(
    string $key,
    ForumGroupVisibility $visibility = ForumGroupVisibility::Public,
    array $questions = [],
    array $taxonIds = [],
): CreateForumGroupData {
    return new CreateForumGroupData(
        name: 'Responsible companion care',
        description: 'A bounded community for practical, respectful, and privacy-aware animal care.',
        rules: ['Protect private information.', 'Use respectful evidence-aware discussion.'],
        visibility: $visibility,
        defaultLocale: 'en',
        locationScope: 'lt-vilnius',
        membershipQuestions: $questions,
        taxonIds: $taxonIds,
        idempotencyKey: $key,
    );
}

function forumGroupInvite(
    User $owner,
    ForumGroup $group,
    User $invitee,
    string $key,
    ?CarbonImmutable $expiresAt = null,
): ForumGroupInvitation {
    return app(InviteForumGroupMember::class)->handle(
        inviter: $owner,
        group: $group,
        invitee: $invitee,
        data: new ForumGroupInvitationData(
            role: ForumGroupRole::Member,
            message: 'Join this bounded private community.',
            expiresAt: $expiresAt ?? CarbonImmutable::now()->addWeek(),
            idempotencyKey: $key,
        ),
    );
}

test('group core schema uses constrained indexed relations', function () {
    expect(Schema::hasColumns('forum_groups', [
        'owner_user_id',
        'stable_key',
        'visibility',
        'status',
        'membership_questions',
        'active_member_count',
        'lock_version',
    ]))->toBeTrue()
        ->and(Schema::hasIndex('forum_groups', 'forum_groups_discovery_idx'))->toBeTrue()
        ->and(Schema::hasIndex(
            'forum_group_memberships',
            'forum_group_memberships_group_user_unique',
        ))->toBeTrue()
        ->and(Schema::hasIndex(
            'forum_group_invitations',
            'forum_group_invitations_recipient_state_idx',
        ))->toBeTrue()
        ->and(Schema::hasIndex(
            'forum_group_events',
            'forum_group_events_history_idx',
        ))->toBeTrue()
        ->and(Schema::hasIndex(
            'forum_group_taxon',
            'forum_group_taxon_taxon_group_idx',
        ))->toBeTrue();
});

test('group enums represent every specified core visibility role and lifecycle state', function () {
    expect(array_column(ForumGroupVisibility::cases(), 'value'))->toBe([
        'public',
        'request-to-join',
        'private',
        'unlisted',
    ])->and(array_column(ForumGroupRole::cases(), 'value'))->toBe([
        'owner',
        'administrator',
        'moderator',
        'steward',
        'member',
        'restricted-member',
    ])->and(array_column(ForumGroupMembershipState::cases(), 'value'))->toBe([
        'pending',
        'active',
        'rejected',
        'removed',
        'banned',
        'left',
    ])->and(array_column(ForumGroupStatus::cases(), 'value'))->toBe([
        'active',
        'closed',
        'archived',
    ]);
});

test('group creation is idempotent and creates owner taxonomy and audit relations', function () {
    $owner = User::factory()->create();
    $taxon = TaxonVersion::query()
        ->where('scientific_name', 'Canis lupus familiaris')
        ->firstOrFail()
        ->taxon;
    $data = forumGroupCreateData(
        'group-create-idempotency-0001',
        ForumGroupVisibility::RequestToJoin,
        ['How will you protect member privacy?'],
        [$taxon->id],
    );
    $group = app(CreateForumGroup::class)->handle($owner, $data);
    $same = app(CreateForumGroup::class)->handle($owner, $data);

    expect($same->id)->toBe($group->id)
        ->and($group->active_member_count)->toBe(1)
        ->and($group->taxa)->toHaveCount(1)
        ->and($group->memberships)->toHaveCount(1)
        ->and($group->memberships->first()->role)->toBe(ForumGroupRole::Owner)
        ->and($group->events)->toHaveCount(1)
        ->and($group->events->first()->event_type)->toBe(ForumGroupEventType::Created)
        ->and(ForumGroup::query()->count())->toBe(7);
});

test('unverified and inactive users cannot create groups', function () {
    expect(fn () => app(CreateForumGroup::class)->handle(
        User::factory()->unverified()->create(),
        forumGroupCreateData('group-create-unverified-0001'),
    ))->toThrow(AuthorizationException::class)
        ->and(fn () => app(CreateForumGroup::class)->handle(
            User::factory()->blocked()->create(),
            forumGroupCreateData('group-create-blocked-000001'),
        ))->toThrow(AuthorizationException::class);
});

test('public membership is immediate idempotent and safely leaveable', function () {
    $group = ForumGroup::factory()->create();
    $member = User::factory()->create();
    $data = new ForumGroupMembershipRequestData(
        answers: ['0' => 'I will contribute practical, respectful care experience.'],
        idempotencyKey: 'group-public-membership-0001',
    );
    $membership = app(RequestForumGroupMembership::class)->handle($member, $group, $data);
    $same = app(RequestForumGroupMembership::class)->handle($member, $group, $data);

    expect($membership->state)->toBe(ForumGroupMembershipState::Active)
        ->and($same->id)->toBe($membership->id)
        ->and($group->refresh()->active_member_count)->toBe(2);

    $left = app(LeaveForumGroup::class)->handle(
        $member,
        $membership,
        $membership->lock_version,
        'group-leave-idempotency-0001',
    );

    expect($left->state)->toBe(ForumGroupMembershipState::Left)
        ->and($group->refresh()->active_member_count)->toBe(1)
        ->and(fn () => app(LeaveForumGroup::class)->handle(
            $group->owner,
            $group->memberships()->where('role', 'owner')->firstOrFail(),
            0,
            'group-owner-leave-denied-0001',
        ))->toThrow(AuthorizationException::class);
});

test('request groups require every answer and authorized review with optimistic locking', function () {
    $group = ForumGroup::factory()->requestToJoin()->create([
        'membership_questions' => ['Why join?', 'How will you help?'],
    ]);
    $applicant = User::factory()->create();

    expect(fn () => app(RequestForumGroupMembership::class)->handle(
        $applicant,
        $group,
        new ForumGroupMembershipRequestData(
            answers: ['0' => 'I care about this topic.'],
            idempotencyKey: 'group-incomplete-request-0001',
        ),
    ))->toThrow(ValidationException::class);

    $membership = app(RequestForumGroupMembership::class)->handle(
        $applicant,
        $group,
        new ForumGroupMembershipRequestData(
            answers: [
                '0' => 'I care about this topic.',
                '1' => 'I will share safe local resources.',
            ],
            idempotencyKey: 'group-complete-request-00001',
        ),
    );

    expect($membership->state)->toBe(ForumGroupMembershipState::Pending)
        ->and($group->refresh()->active_member_count)->toBe(1)
        ->and(fn () => app(ReviewForumGroupMembership::class)->handle(
            User::factory()->create(),
            $membership,
            true,
            'Unauthorized review.',
            0,
            'group-review-denied-000001',
        ))->toThrow(AuthorizationException::class)
        ->and(fn () => app(ReviewForumGroupMembership::class)->handle(
            $group->owner,
            $membership,
            true,
            'Stale review attempt.',
            99,
            'group-review-stale-0000001',
        ))->toThrow(ValidationException::class);

    $approved = app(ReviewForumGroupMembership::class)->handle(
        $group->owner,
        $membership,
        true,
        'Answers meet the group rules.',
        $membership->lock_version,
        'group-review-approved-00001',
    );

    expect($approved->state)->toBe(ForumGroupMembershipState::Active)
        ->and($group->refresh()->active_member_count)->toBe(2)
        ->and($approved->reviewed_by_user_id)->toBe($group->owner_user_id);
});

test('private and unlisted groups never leak through discovery while authorized direct access works', function () {
    $viewer = User::factory()->create();
    $public = ForumGroup::factory()->create();
    $private = ForumGroup::factory()->private()->create();
    $unlisted = ForumGroup::factory()->unlisted()->create();

    $keys = ForumGroup::query()
        ->discoverableTo($viewer)
        ->pluck('stable_key')
        ->all();

    expect($keys)->toContain($public->stable_key)
        ->not->toContain($private->stable_key, $unlisted->stable_key)
        ->and(Gate::forUser($viewer)->allows('view', $private))->toBeFalse()
        ->and(Gate::forUser($viewer)->allows('view', $unlisted))->toBeTrue();

    forumGroupInvite(
        $private->owner,
        $private,
        $viewer,
        'group-private-view-invite-0001',
    );

    expect(Gate::forUser($viewer)->allows('view', $private->refresh()))->toBeTrue();
});

test('private invitations are one-time revocable expire durably and cannot be accepted after closure', function () {
    $group = ForumGroup::factory()->private()->create();
    $invitee = User::factory()->create();
    $invitation = forumGroupInvite(
        $group->owner,
        $group,
        $invitee,
        'group-private-invitation-0001',
    );
    $accepted = app(RespondToForumGroupInvitation::class)->handle(
        $invitee,
        $invitation,
        true,
    );

    expect($accepted->state)->toBe(ForumGroupInvitationState::Accepted)
        ->and($accepted->open_key)->toBeNull()
        ->and($group->refresh()->active_member_count)->toBe(2)
        ->and(fn () => app(RespondToForumGroupInvitation::class)->handle(
            $invitee,
            $accepted,
            false,
        ))->toThrow(ValidationException::class);

    $expiredInvitee = User::factory()->create();
    $expired = ForumGroupInvitation::factory()->expired()->create([
        'forum_group_id' => $group->id,
        'invited_user_id' => $expiredInvitee->id,
        'invited_by_user_id' => $group->owner_user_id,
    ]);

    expect(fn () => app(RespondToForumGroupInvitation::class)->handle(
        $expiredInvitee,
        $expired,
        true,
    ))->toThrow(ValidationException::class)
        ->and($expired->refresh()->state)->toBe(ForumGroupInvitationState::Expired)
        ->and($expired->open_key)->toBeNull();

    $closedInvitee = User::factory()->create();
    $closedInvitation = forumGroupInvite(
        $group->owner,
        $group,
        $closedInvitee,
        'group-closed-invitation-0001',
    );
    app(TransitionForumGroup::class)->handle(
        $group->owner,
        $group,
        ForumGroupStatus::Closed,
        $group->lock_version,
        'Safety review in progress.',
        'group-close-before-accept-0001',
    );

    expect(fn () => app(RespondToForumGroupInvitation::class)->handle(
        $closedInvitee,
        $closedInvitation,
        true,
    ))->toThrow(ValidationException::class)
        ->and($closedInvitation->refresh()->state)->toBe(ForumGroupInvitationState::Pending);
});

test('member role and restriction actions enforce hierarchy audit and counters', function () {
    $group = ForumGroup::factory()->create();
    $member = ForumGroupMembership::factory()->create([
        'forum_group_id' => $group->id,
    ]);
    $changed = app(ChangeForumGroupMemberRole::class)->handle(
        $group->owner,
        $member,
        ForumGroupRole::Steward,
        $member->lock_version,
        'Trusted with invitation support.',
        'group-role-change-000000001',
    );

    expect($changed->role)->toBe(ForumGroupRole::Steward)
        ->and(fn () => app(ChangeForumGroupMemberRole::class)->handle(
            User::factory()->create(),
            $changed,
            ForumGroupRole::Moderator,
            $changed->lock_version,
            'Unauthorized escalation.',
            'group-role-denied-00000001',
        ))->toThrow(AuthorizationException::class);

    $restricted = app(RestrictForumGroupMember::class)->handle(
        $group->owner,
        $changed,
        true,
        'Confirmed breach of the published privacy rule.',
        $changed->lock_version,
        'group-member-ban-000000001',
    );

    expect($restricted->state)->toBe(ForumGroupMembershipState::Banned)
        ->and($group->refresh()->active_member_count)->toBe(1)
        ->and($group->events()->where('event_type', 'member-banned')->count())->toBe(1);
});

test('ownership transfer preserves membership count and creates a durable audit event', function () {
    $group = ForumGroup::factory()->create();
    $newOwner = ForumGroupMembership::factory()->create([
        'forum_group_id' => $group->id,
    ]);
    $oldOwnerId = $group->owner_user_id;
    $count = $group->refresh()->active_member_count;
    $transferred = app(TransferForumGroupOwnership::class)->handle(
        $group->owner,
        $group,
        $newOwner,
        $group->lock_version,
        'Planned ownership succession.',
        'group-owner-transfer-000001',
    );

    expect($transferred->owner_user_id)->toBe($newOwner->user_id)
        ->and($transferred->active_member_count)->toBe($count)
        ->and($transferred->memberships()->where('user_id', $oldOwnerId)->value('role'))
        ->toBe(ForumGroupRole::Administrator)
        ->and($transferred->memberships()->where('user_id', $newOwner->user_id)->value('role'))
        ->toBe(ForumGroupRole::Owner)
        ->and($transferred->events()->where('event_type', 'ownership-transferred')->count())
        ->toBe(1);
});

test('group lifecycle is optimistic audited and archived state is terminal', function () {
    $group = ForumGroup::factory()->create();
    $closed = app(TransitionForumGroup::class)->handle(
        $group->owner,
        $group,
        ForumGroupStatus::Closed,
        0,
        'Temporary safety review.',
        'group-transition-close-00001',
    );
    $reopened = app(TransitionForumGroup::class)->handle(
        $closed->owner,
        $closed,
        ForumGroupStatus::Active,
        1,
        'Safety review completed.',
        'group-transition-reopen-0001',
    );
    $archived = app(TransitionForumGroup::class)->handle(
        $reopened->owner,
        $reopened,
        ForumGroupStatus::Archived,
        2,
        'Community purpose has concluded.',
        'group-transition-archive-001',
    );

    expect($archived->status)->toBe(ForumGroupStatus::Archived)
        ->and($archived->closed_at)->not->toBeNull()
        ->and($archived->archived_at)->not->toBeNull()
        ->and(fn () => app(TransitionForumGroup::class)->handle(
            $archived->owner,
            $archived,
            ForumGroupStatus::Active,
            3,
            'Invalid reopening attempt.',
            'group-transition-invalid-0001',
        ))->toThrow(ValidationException::class)
        ->and($archived->events()->whereIn('event_type', [
            'closed',
            'reopened',
            'archived',
        ])->count())->toBe(3);
});

test('group reports preserve reporter privacy support blocking and append group audit', function () {
    $group = ForumGroup::factory()->create();
    $reporter = User::factory()->create();
    $report = app(SubmitForumReport::class)->handle(
        reporter: $reporter,
        subject: $group,
        reasonKey: 'spam',
        details: 'Repeated unrelated solicitations appeared in the public group description.',
        truthfulnessConfirmed: true,
        blockAffectedUser: true,
    );

    expect($report->subject_type)->toBe(ForumGroup::class)
        ->and($report->affected_user_id)->toBe($group->owner_user_id)
        ->and($report->getHidden())->toContain('reporter_id', 'details')
        ->and(ForumBlock::query()
            ->where('user_key', $reporter->actor_key)
            ->where('blocked_author_key', $group->owner->actor_key)
            ->exists())->toBeTrue()
        ->and($group->events()->where('event_type', 'reported')->count())->toBe(1)
        ->and(ForumReport::query()->count())->toBe(1);
});

test('definition seed is repeatable and preserves lifecycle counters ids and administrator groups', function () {
    $system = ForumGroup::query()->where('stable_key', 'apartment-pets')->firstOrFail();
    $systemId = $system->id;
    $system->forceFill([
        'status' => ForumGroupStatus::Closed,
        'active_member_count' => 17,
        'closed_at' => now(),
    ])->save();
    $administratorGroup = ForumGroup::factory()->create([
        'stable_key' => 'administrator-created-group',
        'is_system_managed' => false,
    ]);

    $this->seed(ForumGroupDefinitionSeeder::class);
    $this->seed(ForumGroupDefinitionSeeder::class);

    expect(ForumGroup::query()->where('is_system_managed', true)->count())->toBe(6)
        ->and($system->refresh()->id)->toBe($systemId)
        ->and($system->status)->toBe(ForumGroupStatus::Closed)
        ->and($system->active_member_count)->toBe(17)
        ->and(ForumGroup::query()->whereKey($administratorGroup->id)->exists())->toBeTrue();
});

test('group factories create valid domain graphs and meaningful states', function () {
    $group = ForumGroup::factory()->private()->create();
    $membership = ForumGroupMembership::factory()->restricted()->create([
        'forum_group_id' => $group->id,
    ]);
    $invitation = ForumGroupInvitation::factory()->create([
        'forum_group_id' => $group->id,
    ]);
    $event = ForumGroupEvent::factory()->create([
        'forum_group_id' => $group->id,
    ]);

    expect($group->memberships()->where('role', 'owner')->count())->toBe(1)
        ->and($membership->role)->toBe(ForumGroupRole::RestrictedMember)
        ->and($group->refresh()->active_member_count)->toBe(2)
        ->and($invitation->invited_by_user_id)->toBe($group->owner_user_id)
        ->and($event->group->is($group))->toBeTrue();
});

test('group events are append only through the model boundary', function () {
    $event = ForumGroupEvent::factory()->create();

    expect(fn () => $event->update(['reason_code' => 'rewritten']))
        ->toThrow(LogicException::class)
        ->and(fn () => $event->delete())
        ->toThrow(LogicException::class);
});

test('database uniqueness rejects duplicate memberships and open invitations', function () {
    $group = ForumGroup::factory()->create();
    $member = User::factory()->create();
    ForumGroupMembership::factory()->create([
        'forum_group_id' => $group->id,
        'user_id' => $member->id,
    ]);

    expect(fn () => ForumGroupMembership::factory()->create([
        'forum_group_id' => $group->id,
        'user_id' => $member->id,
    ]))->toThrow(QueryException::class);
});

test('group routes enforce authentication verification and private visibility', function () {
    $public = ForumGroup::factory()->create();
    $privateOwner = User::factory()->create();
    $private = ForumGroup::factory()->private()->create([
        'owner_user_id' => $privateOwner->id,
    ]);
    $viewer = User::factory()->create();

    auth()->logout();
    $this->get(route('forum.groups.index'))->assertRedirect(route('login'));
    $this->actingAs(User::factory()->unverified()->create())
        ->get(route('forum.groups.index'))
        ->assertRedirect(route('verification.notice'));
    $this->actingAs($viewer)
        ->get(route('forum.groups.index'))
        ->assertOk()
        ->assertSee($public->name)
        ->assertDontSee($private->name);
    $this->actingAs($viewer)
        ->get(route('forum.groups.show', $private))
        ->assertForbidden();
    $this->actingAs($privateOwner)
        ->get(route('forum.groups.show', $private))
        ->assertOk()
        ->assertSee($private->name);
});

test('livewire group creation membership and locked identifiers are server authoritative', function () {
    $owner = User::factory()->create();
    $component = Livewire::actingAs($owner)
        ->test(GroupDirectory::class)
        ->set('form.name', 'Accessible local animal care')
        ->set('form.description', 'A practical group with clear privacy and respectful participation boundaries.')
        ->set('form.rulesText', "Protect private information.\nUse respectful language.")
        ->set('form.visibility', 'public')
        ->set('form.defaultLocale', 'en')
        ->set('form.locationScope', 'lt-vilnius')
        ->call('create');
    $group = ForumGroup::query()
        ->where('name', 'Accessible local animal care')
        ->firstOrFail();

    $component->assertRedirect(route('forum.groups.show', $group));

    $member = User::factory()->create();
    Livewire::actingAs($member)
        ->test(GroupWorkspace::class, ['groupId' => $group->id])
        ->call('requestMembership')
        ->assertSet('feedback', __('forum_groups.feedback.joined'));

    expect(fn () => Livewire::actingAs($member)
        ->test(GroupWorkspace::class, ['groupId' => $group->id])
        ->set('groupId', $group->id + 100))
        ->toThrow(CannotUpdateLockedPropertyException::class);
});

test('group management livewire never exposes controls or accepts actions for ordinary members', function () {
    $owner = User::factory()->create();
    $group = ForumGroup::factory()->create([
        'owner_user_id' => $owner->id,
    ]);
    $outsider = User::factory()->create();
    $invitee = User::factory()->create();

    Livewire::actingAs($outsider);
    $component = Livewire::test(GroupManagement::class, ['groupId' => $group->id]);
    $component
        ->assertDontSee($owner->email);
    $component
        ->set('inviteEmail', $invitee->email)
        ->set('inviteRole', 'member');
    $component
        ->call('invite')
        ->assertForbidden();

    Livewire::actingAs($owner)
        ->test(GroupManagement::class, ['groupId' => $group->id])
        ->assertSee(__('forum_groups.page.manage_heading'))
        ->assertSee($owner->email);
});

test('group translations have complete key and placeholder parity for every locale', function () {
    $catalogues = collect(['en', 'lt', 'ru'])->mapWithKeys(
        static fn (string $locale): array => [
            $locale => Arr::dot(require lang_path("{$locale}/forum_groups.php")),
        ],
    );
    $keys = array_keys($catalogues->get('en'));

    foreach ($catalogues as $locale => $catalogue) {
        expect(array_keys($catalogue))->toBe($keys);

        foreach ($catalogue as $key => $value) {
            preg_match_all('/:[A-Za-z_][A-Za-z0-9_]*/', (string) $value, $matches);
            preg_match_all(
                '/:[A-Za-z_][A-Za-z0-9_]*/',
                (string) $catalogues->get('en')[$key],
                $englishMatches,
            );

            expect($matches[0], "{$locale}:{$key}")->toBe($englishMatches[0])
                ->and((string) $value)->not->toBe("forum_groups.{$key}");
        }
    }
});

test('group directory remains bounded without an n plus one query pattern', function () {
    $viewer = User::factory()->create();
    ForumGroup::factory()->count(18)->create();
    DB::flushQueryLog();
    DB::enableQueryLog();

    Livewire::actingAs($viewer)->test(GroupDirectory::class)->assertOk();
    $queryCount = count(DB::getQueryLog());
    DB::disableQueryLog();

    expect($queryCount)->toBeLessThanOrEqual(18);
});

test('demo group seed is environment gated and repeatable with stable memberships', function () {
    $this->seed(DatabaseSeeder::class);
    $this->seed(ForumGroupDemoSeeder::class);
    $groupCount = ForumGroup::query()->count();
    $membershipCount = ForumGroupMembership::query()->count();
    $invitationCount = ForumGroupInvitation::query()->count();
    $activityCount = ForumGroupActivity::query()->count();
    $announcementCount = ForumGroupAnnouncement::query()->count();
    $fileCount = ForumGroupFile::query()->count();
    $pollCount = ForumPoll::query()->count();

    $this->seed(ForumGroupDemoSeeder::class);

    expect(ForumGroup::query()->count())->toBe($groupCount)
        ->and(ForumGroupMembership::query()->count())->toBe($membershipCount)
        ->and(ForumGroupInvitation::query()->count())->toBe($invitationCount)
        ->and(ForumGroupActivity::query()->count())->toBe($activityCount)
        ->and(ForumGroupAnnouncement::query()->count())->toBe($announcementCount)
        ->and(ForumGroupFile::query()->count())->toBe($fileCount)
        ->and(ForumPoll::query()->count())->toBe($pollCount);
});
