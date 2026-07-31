<?php

declare(strict_types=1);

use App\Actions\CancelSocialRelationshipRequest;
use App\Actions\CreateSocialControl;
use App\Actions\FollowSocialActor;
use App\Actions\RespondToSocialRelationshipRequest;
use App\Actions\SendSocialRelationshipRequest;
use App\Actions\UpdateSocialActorSettings;
use App\Enums\PetManagerRole;
use App\Enums\PetManagerStatus;
use App\Enums\PetProfilePermission;
use App\Enums\SocialFollowPolicy;
use App\Enums\SocialFriendRequestPolicy;
use App\Enums\SocialListVisibility;
use App\Enums\SocialRelationshipStatus;
use App\Enums\SocialRelationshipType;
use App\Enums\SocialRequestStatus;
use App\Livewire\Social\RelationshipCenter;
use App\Models\ExpertProfile;
use App\Models\ForumGroup;
use App\Models\ForumGroupMembership;
use App\Models\PetProfile;
use App\Models\PetProfileManager;
use App\Models\SocialActor;
use App\Models\SocialRelationship;
use App\Models\SocialRelationshipEvent;
use App\Models\SocialRelationshipRequest;
use App\Models\User;
use App\Models\UserDomainState;
use App\Services\PetProfileAccess;
use App\Services\SocialActorDirectory;
use App\Services\SocialActorResolver;
use App\Services\SocialGraphQuery;
use App\Services\SocialRelationshipKey;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('creates indexed social foundation tables and backfills actor adapters idempotently', function (): void {
    $owner = User::factory()->create();
    $pet = PetProfile::factory()->for($owner)->create();
    $expert = ExpertProfile::factory()->for($owner, 'owner')->create();
    $group = ForumGroup::factory()->for($owner, 'owner')->create();
    $legacy = UserDomainState::factory()->for($owner)->create([
        'namespace' => 'pet-friends.state.v1',
        'payload' => ['relationships' => ['demo-pair' => ['status' => 'accepted']]],
    ]);

    expect(Schema::hasColumns('social_relationships', [
        'relationship_key',
        'source_actor_id',
        'target_actor_id',
        'active_key',
        'lock_version',
    ]))->toBeTrue();

    $expectedActorCount = User::query()->count()
        + PetProfile::query()->withTrashed()->count()
        + ExpertProfile::query()->count()
        + ForumGroup::query()->count();
    $this->artisan('social:backfill-actors', ['--chunk' => 1])->assertSuccessful();
    $firstCount = SocialActor::query()->count();
    $this->artisan('social:backfill-actors', ['--chunk' => 1])->assertSuccessful();

    expect($firstCount)->toBe($expectedActorCount)
        ->and(SocialActor::query()->count())->toBe($expectedActorCount)
        ->and($owner->socialActor()->count())->toBe(1)
        ->and($pet->socialActor()->count())->toBe(1)
        ->and($expert->socialActor()->count())->toBe(1)
        ->and($group->socialActor()->count())->toBe(1)
        ->and($legacy->fresh()->payload)->toBe([
            'relationships' => ['demo-pair' => ['status' => 'accepted']],
        ]);
});

it('rolls back only social tables and preserves existing profile data', function (): void {
    $owner = User::factory()->create();
    $pet = PetProfile::factory()->for($owner)->create();
    $legacy = UserDomainState::factory()->for($owner)->create([
        'namespace' => 'pet-friends.state.v1',
        'payload' => ['relationships' => ['preserved-pair' => ['status' => 'accepted']]],
    ]);
    $migration = require database_path(
        'migrations/2026_07_31_182248_create_social_relationship_foundation.php',
    );

    $migration->down();

    expect(Schema::hasTable('social_actors'))->toBeFalse()
        ->and(Schema::hasTable('social_relationship_requests'))->toBeFalse()
        ->and(Schema::hasTable('social_relationships'))->toBeFalse()
        ->and(User::query()->whereKey($owner->id)->exists())->toBeTrue()
        ->and(PetProfile::query()->whereKey($pet->id)->exists())->toBeTrue()
        ->and($legacy->fresh()->payload)->toBe([
            'relationships' => ['preserved-pair' => ['status' => 'accepted']],
        ]);

    $migration->up();

    expect(Schema::hasTable('social_actors'))->toBeTrue()
        ->and(Schema::hasTable('social_relationship_requests'))->toBeTrue()
        ->and(Schema::hasTable('social_relationships'))->toBeTrue()
        ->and(User::query()->whereKey($owner->id)->exists())->toBeTrue()
        ->and(PetProfile::query()->whereKey($pet->id)->exists())->toBeTrue();
});

it('creates one public follow and one event for a replayed action', function (): void {
    $sourceUser = User::factory()->create();
    $targetUser = User::factory()->create();
    $resolver = app(SocialActorResolver::class);
    $source = $resolver->forUser($sourceUser);
    $target = $resolver->forUser($targetUser);
    $this->actingAs($sourceUser);

    $first = app(FollowSocialActor::class)->handle($source, $target, 'follow-replay-001');
    $second = app(FollowSocialActor::class)->handle($source, $target, 'follow-replay-001');

    expect($first)->toBeInstanceOf(SocialRelationship::class)
        ->and($second)->toBeInstanceOf(SocialRelationship::class)
        ->and($second->id)->toBe($first->id)
        ->and(SocialRelationship::query()->count())->toBe(1)
        ->and(SocialRelationshipEvent::query()->count())->toBe(1)
        ->and($first->relationship_type)->toBe(SocialRelationshipType::Follow)
        ->and($first->direction)->toBe(SocialRelationshipType::Follow->direction());
});

it('rejects idempotency keys reused for different actors or operations', function (): void {
    $users = User::factory()->count(4)->create();
    $resolver = app(SocialActorResolver::class);
    $firstSource = $resolver->forUser($users[0]);
    $firstTarget = $resolver->forUser($users[1]);
    $secondSource = $resolver->forUser($users[2]);
    $secondTarget = $resolver->forUser($users[3]);
    $this->actingAs($users[0]);
    app(SendSocialRelationshipRequest::class)->handle(
        $firstSource,
        $firstTarget,
        SocialRelationshipType::OwnerFriendship,
        'shared-request-replay-key',
    );

    $this->actingAs($users[2]);
    expect(fn () => app(SendSocialRelationshipRequest::class)->handle(
        $secondSource,
        $secondTarget,
        SocialRelationshipType::OwnerFriendship,
        'shared-request-replay-key',
    ))->toThrow(ValidationException::class);

    $this->actingAs($users[0]);
    app(FollowSocialActor::class)->handle(
        $firstSource,
        $firstTarget,
        'shared-follow-replay-key',
    );
    $this->actingAs($users[2]);
    expect(fn () => app(FollowSocialActor::class)->handle(
        $secondSource,
        $secondTarget,
        'shared-follow-replay-key',
    ))->toThrow(ValidationException::class);

    expect(SocialRelationshipRequest::query()->count())->toBe(1)
        ->and(SocialRelationship::query()
            ->where('relationship_type', SocialRelationshipType::Follow->value)
            ->count())->toBe(1)
        ->and(SocialRelationshipEvent::query()
            ->where('event_type', 'relationship-created')
            ->count())->toBe(1);
});

it('routes a private follow through approval and keeps opposite subscriptions independent', function (): void {
    $firstUser = User::factory()->create();
    $secondUser = User::factory()->create();
    $resolver = app(SocialActorResolver::class);
    $firstActor = $resolver->forUser($firstUser);
    $secondActor = $resolver->forUser($secondUser);
    $secondActor->settings()->firstOrFail()->forceFill([
        'follow_policy' => SocialFollowPolicy::Approval,
    ])->save();

    $this->actingAs($firstUser);
    $result = app(FollowSocialActor::class)->handle(
        $firstActor,
        $secondActor,
        'private-follow-001',
    );

    expect($result)->toBeInstanceOf(SocialRelationshipRequest::class)
        ->and(SocialRelationship::query()->count())->toBe(0);

    $this->actingAs($secondUser);
    app(RespondToSocialRelationshipRequest::class)->handle(
        $result,
        SocialRequestStatus::Accepted,
        'private-follow-accept-001',
    );

    expect(SocialRelationship::query()->count())->toBe(1)
        ->and(SocialRelationship::query()->firstOrFail()->source_actor_id)->toBe($firstActor->id)
        ->and(SocialRelationship::query()->firstOrFail()->target_actor_id)->toBe($secondActor->id)
        ->and(SocialRelationship::query()
            ->where('source_actor_id', $secondActor->id)
            ->where('target_actor_id', $firstActor->id)
            ->count())->toBe(0);
});

it('deduplicates mutual friendship requests and creates one symmetric relationship', function (): void {
    $firstUser = User::factory()->create();
    $secondUser = User::factory()->create();
    $resolver = app(SocialActorResolver::class);
    $firstActor = $resolver->forUser($firstUser);
    $secondActor = $resolver->forUser($secondUser);
    $this->actingAs($firstUser);
    $action = app(SendSocialRelationshipRequest::class);

    $first = $action->handle(
        $firstActor,
        $secondActor,
        SocialRelationshipType::OwnerFriendship,
        'friend-request-001',
    );
    $duplicate = $action->handle(
        $firstActor,
        $secondActor,
        SocialRelationshipType::OwnerFriendship,
        'friend-request-002',
    );

    expect($duplicate->id)->toBe($first->id)
        ->and(SocialRelationshipRequest::query()->count())->toBe(1);

    $this->actingAs($secondUser);
    app(RespondToSocialRelationshipRequest::class)->handle(
        $first,
        SocialRequestStatus::Accepted,
        'friend-accept-001',
    );
    $relationship = SocialRelationship::query()->firstOrFail();

    expect($relationship->relationship_type)->toBe(SocialRelationshipType::OwnerFriendship)
        ->and($relationship->active_key)->toBe(SocialRelationshipKey::forRelationship(
            $firstActor->id,
            $secondActor->id,
            SocialRelationshipType::OwnerFriendship,
        ))
        ->and(SocialRelationshipEvent::query()->where('event_type', 'relationship-created')->count())->toBe(1);
});

it('enforces friends of friends request policy from durable friendships', function (): void {
    $firstUser = User::factory()->create();
    $mutualUser = User::factory()->create();
    $targetUser = User::factory()->create();
    $resolver = app(SocialActorResolver::class);
    $firstActor = $resolver->forUser($firstUser);
    $mutualActor = $resolver->forUser($mutualUser);
    $targetActor = $resolver->forUser($targetUser);

    $this->actingAs($firstUser);
    $firstRequest = app(SendSocialRelationshipRequest::class)->handle(
        $firstActor,
        $mutualActor,
        SocialRelationshipType::OwnerFriendship,
        'mutual-first-request',
    );
    $this->actingAs($mutualUser);
    app(RespondToSocialRelationshipRequest::class)->handle(
        $firstRequest,
        SocialRequestStatus::Accepted,
        'mutual-first-accept',
    );
    $secondRequest = app(SendSocialRelationshipRequest::class)->handle(
        $mutualActor,
        $targetActor,
        SocialRelationshipType::OwnerFriendship,
        'mutual-second-request',
    );
    $this->actingAs($targetUser);
    app(RespondToSocialRelationshipRequest::class)->handle(
        $secondRequest,
        SocialRequestStatus::Accepted,
        'mutual-second-accept',
    );
    $targetActor->settings()->firstOrFail()->forceFill([
        'friend_request_policy' => SocialFriendRequestPolicy::FriendsOfFriends,
    ])->save();

    $this->actingAs($firstUser);
    $friendOfFriendRequest = app(SendSocialRelationshipRequest::class)->handle(
        $firstActor,
        $targetActor,
        SocialRelationshipType::OwnerFriendship,
        'friend-of-friend-request',
    );

    expect($friendOfFriendRequest->status)->toBe(SocialRequestStatus::Pending);
});

it('enforces shared group request policy from active memberships', function (): void {
    $firstUser = User::factory()->create();
    $targetUser = User::factory()->create();
    $resolver = app(SocialActorResolver::class);
    $firstActor = $resolver->forUser($firstUser);
    $targetActor = $resolver->forUser($targetUser);
    $targetActor->settings()->firstOrFail()->forceFill([
        'friend_request_policy' => SocialFriendRequestPolicy::SharedGroups,
    ])->save();
    $this->actingAs($firstUser);

    expect(fn () => app(SendSocialRelationshipRequest::class)->handle(
        $firstActor,
        $targetActor,
        SocialRelationshipType::OwnerFriendship,
        'shared-group-before-membership',
    ))->toThrow(ValidationException::class);

    $group = ForumGroup::factory()->for($firstUser, 'owner')->create();
    ForumGroupMembership::factory()
        ->for($group, 'group')
        ->for($targetUser)
        ->create();
    $sharedGroupRequest = app(SendSocialRelationshipRequest::class)->handle(
        $firstActor,
        $targetActor,
        SocialRelationshipType::OwnerFriendship,
        'shared-group-after-membership',
    );

    expect($sharedGroupRequest->status)->toBe(SocialRequestStatus::Pending);
});

it('does not treat client supplied context as a verified personal invitation', function (): void {
    $sourceUser = User::factory()->create();
    $targetUser = User::factory()->create();
    $resolver = app(SocialActorResolver::class);
    $sourceActor = $resolver->forUser($sourceUser);
    $targetActor = $resolver->forUser($targetUser);
    $targetActor->settings()->firstOrFail()->forceFill([
        'friend_request_policy' => SocialFriendRequestPolicy::LinkOnly,
    ])->save();
    $this->actingAs($sourceUser);

    expect(fn () => app(SendSocialRelationshipRequest::class)->handle(
        source: $sourceActor,
        target: $targetActor,
        type: SocialRelationshipType::OwnerFriendship,
        idempotencyKey: 'forged-personal-link',
        contextType: 'personal-link',
        contextKey: 'client-supplied-token',
    ))->toThrow(ValidationException::class);
});

it('requires authorized managers on both pet profiles for pet friendship', function (): void {
    $firstOwner = User::factory()->create();
    $secondOwner = User::factory()->create();
    $outsider = User::factory()->create();
    $firstPet = PetProfile::factory()->for($firstOwner)->create();
    $secondPet = PetProfile::factory()->for($secondOwner)->create();
    $resolver = app(SocialActorResolver::class);
    $firstActor = $resolver->forPet($firstPet);
    $secondActor = $resolver->forPet($secondPet);

    $this->actingAs($firstOwner);
    $request = app(SendSocialRelationshipRequest::class)->handle(
        $firstActor,
        $secondActor,
        SocialRelationshipType::PetFriendship,
        'pet-friend-request-001',
    );

    $this->actingAs($outsider);
    expect(fn () => app(RespondToSocialRelationshipRequest::class)->handle(
        $request,
        SocialRequestStatus::Accepted,
        'pet-friend-accept-outsider',
    ))->toThrow(AuthorizationException::class);

    $this->actingAs($secondOwner);
    app(RespondToSocialRelationshipRequest::class)->handle(
        $request,
        SocialRequestStatus::Accepted,
        'pet-friend-accept-001',
    );

    expect(SocialRelationship::query()->firstOrFail()->relationship_type)
        ->toBe(SocialRelationshipType::PetFriendship);
});

it('blocks pet social actions immediately after manager access is revoked', function (): void {
    $owner = User::factory()->create();
    $manager = User::factory()->create();
    $targetOwner = User::factory()->create();
    $pet = PetProfile::factory()->for($owner)->create();
    $targetPet = PetProfile::factory()->for($targetOwner)->create();
    $membership = PetProfileManager::factory()->for($pet, 'profile')->for($manager)->create([
        'role' => PetManagerRole::FamilyMember,
        'status' => PetManagerStatus::Active,
    ]);
    $resolver = app(SocialActorResolver::class);
    $source = $resolver->forPet($pet);
    $target = $resolver->forPet($targetPet);
    $this->actingAs($manager);

    expect(app(PetProfileAccess::class)->allows(
        $pet,
        $manager,
        PetProfilePermission::ManageSocial,
    ))->toBeTrue();

    $membership->forceFill([
        'status' => PetManagerStatus::Revoked,
        'revoked_at' => now(),
    ])->save();
    $pet->unsetRelation('managers');

    expect(fn () => app(SendSocialRelationshipRequest::class)->handle(
        $source,
        $target,
        SocialRelationshipType::PetFriendship,
        'revoked-manager-request',
    ))->toThrow(AuthorizationException::class);
});

it('keeps close circle separate from pet administration permissions', function (): void {
    $firstUser = User::factory()->create();
    $secondUser = User::factory()->create();
    $secondPet = PetProfile::factory()->for($secondUser)->create();
    $resolver = app(SocialActorResolver::class);
    $source = $resolver->forUser($firstUser);
    $target = $resolver->forUser($secondUser);
    $this->actingAs($firstUser);

    $relationship = app(CreateSocialControl::class)->handle(
        $source,
        $target,
        SocialRelationshipType::CloseCircle,
        'close-circle-001',
    );

    expect($relationship->relationship_type)->toBe(SocialRelationshipType::CloseCircle)
        ->and(app(PetProfileAccess::class)->allows(
            $secondPet,
            $firstUser,
            PetProfilePermission::EditBasics,
        ))->toBeFalse();
});

it('ends contact on block and prevents new requests without exposing the block to its target', function (): void {
    $firstUser = User::factory()->create();
    $secondUser = User::factory()->create();
    $resolver = app(SocialActorResolver::class);
    $firstActor = $resolver->forUser($firstUser);
    $secondActor = $resolver->forUser($secondUser);
    $this->actingAs($firstUser);
    $request = app(SendSocialRelationshipRequest::class)->handle(
        $firstActor,
        $secondActor,
        SocialRelationshipType::OwnerFriendship,
        'block-friend-request',
    );
    $this->actingAs($secondUser);
    app(RespondToSocialRelationshipRequest::class)->handle(
        $request,
        SocialRequestStatus::Accepted,
        'block-friend-accept',
    );

    $this->actingAs($firstUser);
    $block = app(CreateSocialControl::class)->handle(
        $firstActor,
        $secondActor,
        SocialRelationshipType::Block,
        'block-contact-001',
    );

    expect(SocialRelationship::query()
        ->where('relationship_type', SocialRelationshipType::OwnerFriendship->value)
        ->firstOrFail()->status)->toBe(SocialRelationshipStatus::Ended)
        ->and($block->status)->toBe(SocialRelationshipStatus::Active);

    expect(fn () => app(SendSocialRelationshipRequest::class)->handle(
        $firstActor,
        $secondActor,
        SocialRelationshipType::OwnerFriendship,
        'blocked-new-request',
    ))->toThrow(ValidationException::class);

    $this->actingAs($secondUser);
    expect($secondUser->can('view', $block))->toBeFalse();
});

it('serializes cancel before accept and leaves one final request state', function (): void {
    $firstUser = User::factory()->create();
    $secondUser = User::factory()->create();
    $resolver = app(SocialActorResolver::class);
    $firstActor = $resolver->forUser($firstUser);
    $secondActor = $resolver->forUser($secondUser);
    $this->actingAs($firstUser);
    $request = app(SendSocialRelationshipRequest::class)->handle(
        $firstActor,
        $secondActor,
        SocialRelationshipType::OwnerFriendship,
        'cancel-race-request',
    );
    app(CancelSocialRelationshipRequest::class)->handle($request, 'cancel-race-winner');

    $this->actingAs($secondUser);
    expect(fn () => app(RespondToSocialRelationshipRequest::class)->handle(
        $request,
        SocialRequestStatus::Accepted,
        'cancel-race-loser',
    ))->toThrow(ValidationException::class);

    expect($request->fresh()->status)->toBe(SocialRequestStatus::Cancelled)
        ->and(SocialRelationship::query()->count())->toBe(0)
        ->and(SocialRelationshipRequest::query()->count())->toBe(1);
});

it('persists an expired request and its audit event before reporting the error', function (): void {
    $firstUser = User::factory()->create();
    $secondUser = User::factory()->create();
    $resolver = app(SocialActorResolver::class);
    $firstActor = $resolver->forUser($firstUser);
    $secondActor = $resolver->forUser($secondUser);
    $this->actingAs($firstUser);
    $request = app(SendSocialRelationshipRequest::class)->handle(
        $firstActor,
        $secondActor,
        SocialRelationshipType::OwnerFriendship,
        'expired-request-create',
    );
    $request->forceFill(['expires_at' => now()->subSecond()])->save();

    $this->actingAs($secondUser);
    expect(fn () => app(RespondToSocialRelationshipRequest::class)->handle(
        $request,
        SocialRequestStatus::Accepted,
        'expired-request-response',
    ))->toThrow(ValidationException::class);

    expect($request->fresh()->status)->toBe(SocialRequestStatus::Expired)
        ->and($request->fresh()->active_key)->toBeNull()
        ->and(SocialRelationship::query()->count())->toBe(0)
        ->and(SocialRelationshipEvent::query()
            ->where('event_type', 'request-expired')
            ->count())->toBe(1);

    expect(fn () => app(RespondToSocialRelationshipRequest::class)->handle(
        $request,
        SocialRequestStatus::Accepted,
        'expired-request-response',
    ))->toThrow(ValidationException::class);

    expect(SocialRelationshipEvent::query()
        ->where('event_type', 'request-expired')
        ->count())->toBe(1);
});

it('updates actor privacy optimistically and invalidates graph projections', function (): void {
    $owner = User::factory()->create();
    $resolver = app(SocialActorResolver::class);
    $actor = $resolver->forUser($owner);
    $settings = $actor->settings()->firstOrFail();
    Cache::forever("social:actor:{$actor->id}:counts", ['stale' => true]);
    $this->actingAs($owner);

    $updated = app(UpdateSocialActorSettings::class)->handle(
        actor: $actor,
        friendRequestPolicy: SocialFriendRequestPolicy::Nobody,
        followPolicy: SocialFollowPolicy::Approval,
        friendListVisibility: SocialListVisibility::Hidden,
        followerListVisibility: SocialListVisibility::CountOnly,
        isRecommendable: false,
        allowMessageRequests: false,
        expectedLockVersion: $settings->lock_version,
    );

    expect($updated->friend_request_policy)->toBe(SocialFriendRequestPolicy::Nobody)
        ->and($updated->is_recommendable)->toBeFalse()
        ->and(Cache::has("social:actor:{$actor->id}:counts"))->toBeFalse();

    expect(fn () => app(UpdateSocialActorSettings::class)->handle(
        actor: $actor->refresh(),
        friendRequestPolicy: SocialFriendRequestPolicy::Everyone,
        followPolicy: SocialFollowPolicy::Public,
        friendListVisibility: SocialListVisibility::Everyone,
        followerListVisibility: SocialListVisibility::Everyone,
        isRecommendable: true,
        allowMessageRequests: true,
        expectedLockVersion: $settings->lock_version,
    ))->toThrow(ValidationException::class);
});

it('keeps hidden directed controls out of the target graph projection', function (): void {
    $firstUser = User::factory()->create();
    $secondUser = User::factory()->create();
    $resolver = app(SocialActorResolver::class);
    $firstActor = $resolver->forUser($firstUser);
    $secondActor = $resolver->forUser($secondUser);
    $this->actingAs($firstUser);
    app(CreateSocialControl::class)->handle(
        $firstActor,
        $secondActor,
        SocialRelationshipType::Mute,
        'mute-hidden-001',
    );

    $sourceGraph = app(SocialGraphQuery::class)->relationships($firstActor, $firstUser);
    $targetGraph = app(SocialGraphQuery::class)->relationships($secondActor, $secondUser);

    expect($sourceGraph)->toHaveCount(1)
        ->and($targetGraph)->toHaveCount(0);
});

it('searches public actors with bounded queries and excludes hidden or blocked profiles', function (): void {
    $sourceUser = User::factory()->create(['name' => 'Directory Source']);
    $visibleUser = User::factory()->create(['name' => 'Directory Match']);
    $hiddenUser = User::factory()->create(['name' => 'Directory Hidden']);
    $blockedUser = User::factory()->create(['name' => 'Directory Blocked']);
    $resolver = app(SocialActorResolver::class);
    $source = $resolver->forUser($sourceUser);
    $visible = $resolver->forUser($visibleUser);
    $hidden = $resolver->forUser($hiddenUser);
    $blocked = $resolver->forUser($blockedUser);
    $hidden->forceFill(['is_discoverable' => false])->save();
    $this->actingAs($sourceUser);
    app(CreateSocialControl::class)->handle(
        $source,
        $blocked,
        SocialRelationshipType::Block,
        'directory-blocked-actor',
    );

    DB::flushQueryLog();
    DB::enableQueryLog();
    $results = app(SocialActorDirectory::class)->search($source, $sourceUser, 'Directory');
    $queryCount = count(DB::getQueryLog());
    DB::disableQueryLog();

    expect($results)->toHaveCount(1)
        ->and($results[0]['key'])->toBe($visible->actor_key)
        ->and($results[0]['name'])->toBe('Directory Match')
        ->and($queryCount)->toBeLessThanOrEqual(9);
});

it('creates a follow and a separate friendship request from the livewire directory', function (): void {
    $sourceUser = User::factory()->create(['name' => 'Livewire Source']);
    $targetUser = User::factory()->create(['name' => 'Livewire Directory Target']);
    $resolver = app(SocialActorResolver::class);
    $source = $resolver->forUser($sourceUser);
    $target = $resolver->forUser($targetUser);

    Livewire::actingAs($sourceUser)
        ->test(RelationshipCenter::class)
        ->set('actorSearch', 'Livewire Directory')
        ->assertSee('Livewire Directory Target')
        ->call('followActor', $target->actor_key)
        ->assertHasNoErrors()
        ->call('requestFriendship', $target->actor_key)
        ->assertHasNoErrors();

    expect(SocialRelationship::query()
        ->where('source_actor_id', $source->id)
        ->where('target_actor_id', $target->id)
        ->where('relationship_type', SocialRelationshipType::Follow->value)
        ->count())->toBe(1)
        ->and(SocialRelationshipRequest::query()
            ->where('source_actor_id', $source->id)
            ->where('target_actor_id', $target->id)
            ->where('relationship_type', SocialRelationshipType::OwnerFriendship->value)
            ->count())->toBe(1);
});

it('renders the social center and reauthorizes direct livewire request actions', function (): void {
    $sourceUser = User::factory()->create();
    $targetUser = User::factory()->create();
    $outsider = User::factory()->create();
    $resolver = app(SocialActorResolver::class);
    $sourceActor = $resolver->forUser($sourceUser);
    $targetActor = $resolver->forUser($targetUser);
    $resolver->forUser($outsider);
    $this->actingAs($sourceUser);
    $request = app(SendSocialRelationshipRequest::class)->handle(
        $sourceActor,
        $targetActor,
        SocialRelationshipType::OwnerFriendship,
        'livewire-auth-request',
    );

    $this->actingAs($targetUser);
    $this->get(route('circle.index'))
        ->assertOk()
        ->assertSee(route('social.index'), false);
    $this->get(route('social.index'))
        ->assertOk()
        ->assertSee(__('social_relationships.title'));

    Livewire::actingAs($outsider)
        ->test(RelationshipCenter::class)
        ->call('accept', $request->request_key)
        ->assertForbidden();
});

it('keeps relationship events append only', function (): void {
    $sourceUser = User::factory()->create();
    $targetUser = User::factory()->create();
    $resolver = app(SocialActorResolver::class);
    $source = $resolver->forUser($sourceUser);
    $target = $resolver->forUser($targetUser);
    $this->actingAs($sourceUser);
    app(FollowSocialActor::class)->handle($source, $target, 'immutable-event-follow');
    $event = SocialRelationshipEvent::query()->firstOrFail();

    expect(fn () => $event->forceFill(['event_type' => 'rewritten'])->save())
        ->toThrow(LogicException::class)
        ->and(fn () => $event->delete())
        ->toThrow(LogicException::class);
});

it('keeps social relationship translations aligned across supported locales', function (): void {
    $catalogues = collect(['en', 'lt', 'ru'])->mapWithKeys(
        static fn (string $locale): array => [
            $locale => Arr::dot(require lang_path("{$locale}/social_relationships.php")),
        ],
    );
    $english = $catalogues->get('en');

    expect($english)->toBeArray();

    foreach ($catalogues as $locale => $catalogue) {
        expect(array_keys($catalogue), $locale)->toBe(array_keys($english));

        foreach ($english as $key => $value) {
            preg_match_all('/:[A-Za-z_][A-Za-z0-9_]*/', (string) $value, $expectedMatches);
            preg_match_all('/:[A-Za-z_][A-Za-z0-9_]*/', (string) $catalogue[$key], $actualMatches);

            expect(array_values(array_unique($actualMatches[0])), "{$locale}:{$key}")
                ->toBe(array_values(array_unique($expectedMatches[0])));
        }
    }
});
