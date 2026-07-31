<?php

declare(strict_types=1);

use App\Actions\InviteForumGroupMember;
use App\Actions\RequestForumGroupMembership;
use App\Actions\RespondToForumGroupInvitation;
use App\Data\ForumGroupInvitationData;
use App\Data\ForumGroupMembershipRequestData;
use App\Enums\ForumGroupMembershipState;
use App\Enums\ForumGroupRole;
use App\Livewire\Forum\GroupWorkspace;
use App\Models\ForumGroup;
use App\Models\ForumGroupMembership;
use App\Models\PetProfile;
use App\Models\User;
use App\Services\CommunityMembershipActorEligibility;
use App\Services\SocialActorResolver;
use Carbon\CarbonImmutable;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('community membership schema stores profile context and accepted rules version', function () {
    expect(Schema::hasColumns('forum_groups', [
        'rules_version',
        'allowed_actor_types',
    ]))->toBeTrue()
        ->and(Schema::hasColumns('forum_group_memberships', [
            'social_actor_id',
            'accepted_rules_version',
            'accepted_rules_at',
        ]))->toBeTrue()
        ->and(Schema::hasIndex(
            'forum_group_memberships',
            'forum_group_memberships_actor_fk_idx',
        ))->toBeTrue();
});

test('community membership migration rolls back compatible rows and restores them', function () {
    $group = ForumGroup::factory()->create();
    $membershipId = $group->memberships()->value('id');
    $migration = require database_path(
        'migrations/2026_08_01_000200_add_profile_context_to_forum_group_memberships.php',
    );

    $migration->down();

    expect(Schema::hasColumn('forum_groups', 'rules_version'))->toBeFalse()
        ->and(Schema::hasColumn('forum_group_memberships', 'social_actor_id'))->toBeFalse()
        ->and(Schema::hasIndex(
            'forum_group_memberships',
            'forum_group_memberships_group_user_unique',
        ))->toBeTrue()
        ->and(ForumGroupMembership::query()->whereKey($membershipId)->exists())->toBeTrue();

    $migration->up();

    $membership = ForumGroupMembership::query()->findOrFail($membershipId);

    expect(Schema::hasColumn('forum_groups', 'rules_version'))->toBeTrue()
        ->and(Schema::hasColumn('forum_group_memberships', 'social_actor_id'))->toBeTrue()
        ->and($membership->social_actor_id)->toBeInt()
        ->and($membership->accepted_rules_version)->toBe(1);
});

test('one account can join the same community through distinct controlled profiles', function () {
    $user = User::factory()->create();
    $pet = PetProfile::factory()->create(['user_id' => $user->id, 'name' => 'Baks']);
    $group = ForumGroup::factory()->create([
        'rules_version' => 3,
        'allowed_actor_types' => ['user', 'pet'],
    ]);
    $actors = app(SocialActorResolver::class);
    $personalActor = $actors->forUser($user);
    $petActor = $actors->forPet($pet);
    $action = app(RequestForumGroupMembership::class);

    $personalMembership = $action->handle(
        $user,
        $group,
        new ForumGroupMembershipRequestData(
            answers: ['0' => 'I will contribute responsibly.'],
            idempotencyKey: 'community-personal-membership-0001',
            socialActorKey: $personalActor->actor_key,
        ),
    );
    $petMembership = $action->handle(
        $user,
        $group,
        new ForumGroupMembershipRequestData(
            answers: ['0' => 'Baks joins the local club.'],
            idempotencyKey: 'community-pet-membership-0000001',
            socialActorKey: $petActor->actor_key,
        ),
    );

    expect($personalMembership->id)->not->toBe($petMembership->id)
        ->and($personalMembership->user_id)->toBe($user->id)
        ->and($petMembership->user_id)->toBe($user->id)
        ->and($personalMembership->social_actor_id)->toBe($personalActor->id)
        ->and($petMembership->social_actor_id)->toBe($petActor->id)
        ->and($petMembership->accepted_rules_version)->toBe(3)
        ->and($petMembership->accepted_rules_at)->not->toBeNull()
        ->and($group->refresh()->active_member_count)->toBe(3)
        ->and(ForumGroupMembership::query()
            ->where('forum_group_id', $group->id)
            ->where('user_id', $user->id)
            ->where('state', ForumGroupMembershipState::Active->value)
            ->count())->toBe(2);
});

test('rollback refuses to discard distinct profile memberships for one account', function () {
    $user = User::factory()->create();
    $pet = PetProfile::factory()->create(['user_id' => $user->id]);
    $group = ForumGroup::factory()->create();
    $actors = app(SocialActorResolver::class);
    $action = app(RequestForumGroupMembership::class);

    foreach ([$actors->forUser($user), $actors->forPet($pet)] as $index => $actor) {
        $action->handle(
            $user,
            $group,
            new ForumGroupMembershipRequestData(
                answers: ['0' => 'Preserve this profile membership.'],
                idempotencyKey: "community-rollback-guard-000{$index}",
                socialActorKey: $actor->actor_key,
            ),
        );
    }

    $migration = require database_path(
        'migrations/2026_08_01_000200_add_profile_context_to_forum_group_memberships.php',
    );

    expect(fn () => $migration->down())->toThrow(
        RuntimeException::class,
        'Cannot restore account-scoped community membership without losing profile memberships.',
    )
        ->and(Schema::hasColumn('forum_group_memberships', 'social_actor_id'))->toBeTrue()
        ->and(ForumGroupMembership::query()
            ->where('forum_group_id', $group->id)
            ->where('user_id', $user->id)
            ->count())->toBe(2);
});

test('membership rejects a profile controlled by another account or disallowed by the group', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    $foreignPet = PetProfile::factory()->create(['user_id' => $other->id]);
    $ownPet = PetProfile::factory()->create(['user_id' => $user->id]);
    $group = ForumGroup::factory()->create(['allowed_actor_types' => ['pet']]);
    $actors = app(SocialActorResolver::class);

    expect(fn () => app(RequestForumGroupMembership::class)->handle(
        $user,
        $group,
        new ForumGroupMembershipRequestData(
            answers: ['0' => 'Not authorized.'],
            idempotencyKey: 'community-foreign-profile-000001',
            socialActorKey: $actors->forPet($foreignPet)->actor_key,
        ),
    ))->toThrow(AuthorizationException::class)
        ->and(fn () => app(RequestForumGroupMembership::class)->handle(
            $user,
            $group,
            new ForumGroupMembershipRequestData(
                answers: ['0' => 'Wrong profile type.'],
                idempotencyKey: 'community-wrong-profile-type-001',
                socialActorKey: $actors->forUser($user)->actor_key,
            ),
        ))->toThrow(AuthorizationException::class);

    $membership = app(RequestForumGroupMembership::class)->handle(
        $user,
        $group,
        new ForumGroupMembershipRequestData(
            answers: ['0' => 'Controlled pet profile.'],
            idempotencyKey: 'community-own-profile-type-00001',
            socialActorKey: $actors->forPet($ownPet)->actor_key,
        ),
    );

    expect($membership->socialActor->pet_profile_id)->toBe($ownPet->id);
});

test('an invitation can be accepted through an eligible pet profile without hiding the account', function () {
    $invitee = User::factory()->create();
    $pet = PetProfile::factory()->create(['user_id' => $invitee->id]);
    $group = ForumGroup::factory()->private()->create([
        'rules_version' => 4,
        'allowed_actor_types' => ['pet'],
    ]);
    $invitation = app(InviteForumGroupMember::class)->handle(
        inviter: $group->owner,
        group: $group,
        invitee: $invitee,
        data: new ForumGroupInvitationData(
            role: ForumGroupRole::Member,
            message: 'Join through an eligible pet profile.',
            expiresAt: CarbonImmutable::now()->addWeek(),
            idempotencyKey: 'community-pet-invitation-000001',
        ),
    );
    $petActor = app(SocialActorResolver::class)->forPet($pet);

    app(RespondToForumGroupInvitation::class)->handle(
        $invitee,
        $invitation,
        true,
        $petActor,
    );

    $membership = ForumGroupMembership::query()
        ->where('forum_group_id', $group->id)
        ->where('social_actor_id', $petActor->id)
        ->firstOrFail();

    expect($membership->user_id)->toBe($invitee->id)
        ->and($membership->accepted_rules_version)->toBe(4)
        ->and($membership->socialActor->pet_profile_id)->toBe($pet->id)
        ->and($group->refresh()->active_member_count)->toBe(2);
});

test('eligible community profiles use a bounded query count without per-pet actor queries', function () {
    $user = User::factory()->create();
    PetProfile::factory()->count(25)->create(['user_id' => $user->id]);
    $group = ForumGroup::factory()->create([
        'allowed_actor_types' => ['user', 'pet'],
        'membership_questions' => [],
    ]);

    DB::flushQueryLog();
    DB::enableQueryLog();
    $actors = app(CommunityMembershipActorEligibility::class)->availableTo($user, $group);
    $queryCount = count(DB::getQueryLog());
    DB::disableQueryLog();

    expect($actors)->toHaveCount(26)
        ->and($queryCount)->toBeLessThanOrEqual(20);
});

test('livewire lets the account choose a profile while keeping the real author server authoritative', function () {
    $user = User::factory()->create();
    $pet = PetProfile::factory()->create(['user_id' => $user->id, 'name' => 'Luna']);
    $group = ForumGroup::factory()->create([
        'allowed_actor_types' => ['user', 'pet'],
        'membership_questions' => [],
    ]);
    $petActor = app(SocialActorResolver::class)->forPet($pet);

    Livewire::actingAs($user)
        ->test(GroupWorkspace::class, ['groupId' => $group->id])
        ->assertSee('Luna')
        ->set('selectedActorKey', $petActor->actor_key)
        ->call('requestMembership')
        ->assertSet('feedback', __('forum_groups.feedback.joined'));

    $membership = ForumGroupMembership::query()
        ->where('forum_group_id', $group->id)
        ->where('social_actor_id', $petActor->id)
        ->firstOrFail();

    expect($membership->user_id)->toBe($user->id)
        ->and($membership->social_actor_id)->toBe($petActor->id);
});
