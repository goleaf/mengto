<?php

declare(strict_types=1);

use App\Actions\BlockSocialAccount;
use App\Actions\ReportSocialRelationshipRequest;
use App\Actions\RespondToSocialRelationshipRequest;
use App\Actions\RevokeSocialAccountBlock;
use App\Actions\SendSocialRelationshipRequest;
use App\Enums\PetManagerStatus;
use App\Enums\SocialAccountBlockStatus;
use App\Enums\SocialRelationshipStatus;
use App\Enums\SocialRelationshipType;
use App\Enums\SocialRequestStatus;
use App\Livewire\Social\RelationshipCenter;
use App\Models\ExpertProfile;
use App\Models\ForumGroup;
use App\Models\ForumReport;
use App\Models\ForumReportReason;
use App\Models\PetProfile;
use App\Models\PetProfileManager;
use App\Models\SocialAccountBlock;
use App\Models\SocialActor;
use App\Models\SocialRelationship;
use App\Models\SocialRelationshipEvent;
use App\Models\SocialRelationshipRequest;
use App\Models\User;
use App\Services\SocialActorDirectory;
use App\Services\SocialActorResolver;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function safetyPublicActor(User $user): SocialActor
{
    $actor = app(SocialActorResolver::class)->provisionPrivateForUser($user);
    $actor->forceFill(['is_discoverable' => true])->saveOrFail();
    $actor->settings()->firstOrFail()->forceFill([
        'friend_request_policy' => 'everyone',
        'follow_policy' => 'public',
        'is_recommendable' => true,
        'allow_message_requests' => true,
    ])->saveOrFail();

    return $actor;
}

it('migrates and rolls back account safety without removing the social foundation', function (): void {
    $user = User::factory()->create();
    $actor = safetyPublicActor($user);
    $migration = require database_path(
        'migrations/2026_07_31_235900_add_social_request_safety.php',
    );
    $indexMigration = require database_path(
        'migrations/2026_07_31_235910_add_social_account_block_foreign_key_indexes.php',
    );

    expect(Schema::hasTable('social_account_blocks'))->toBeTrue()
        ->and(Schema::hasColumns('social_relationship_requests', [
            'message_fingerprint',
            'risk_level',
            'risk_signals',
            'prevent_repeats',
        ]))->toBeTrue()
        ->and(Schema::hasColumn('forum_reports', 'idempotency_key'))->toBeTrue()
        ->and(Schema::hasIndex('social_account_blocks', 'social_account_blocks_source_actor_fk_idx'))->toBeTrue()
        ->and(Schema::hasIndex('social_account_blocks', 'social_account_blocks_target_actor_fk_idx'))->toBeTrue()
        ->and(Schema::hasIndex('social_account_blocks', 'social_account_blocks_creator_fk_idx'))->toBeTrue()
        ->and(Schema::hasIndex('social_account_blocks', 'social_account_blocks_revoker_fk_idx'))->toBeTrue();

    $indexMigration->down();

    $migration->down();

    expect(Schema::hasTable('social_account_blocks'))->toBeFalse()
        ->and(Schema::hasColumn('social_relationship_requests', 'message_fingerprint'))->toBeFalse()
        ->and(Schema::hasColumn('forum_reports', 'idempotency_key'))->toBeFalse()
        ->and(Schema::hasTable('social_actors'))->toBeTrue()
        ->and(User::query()->whereKey($user->id)->exists())->toBeTrue()
        ->and($actor->fresh())->not->toBeNull();

    $migration->up();
    $indexMigration->up();

    expect(Schema::hasTable('social_account_blocks'))->toBeTrue()
        ->and(Schema::hasColumn('social_relationship_requests', 'message_fingerprint'))->toBeTrue()
        ->and(Schema::hasColumn('forum_reports', 'idempotency_key'))->toBeTrue()
        ->and(Schema::hasIndex('social_account_blocks', 'social_account_blocks_source_actor_fk_idx'))->toBeTrue()
        ->and(Schema::hasIndex('social_account_blocks', 'social_account_blocks_target_actor_fk_idx'))->toBeTrue()
        ->and(Schema::hasIndex('social_account_blocks', 'social_account_blocks_creator_fk_idx'))->toBeTrue()
        ->and(Schema::hasIndex('social_account_blocks', 'social_account_blocks_revoker_fk_idx'))->toBeTrue();
});

it('blocks every current and future managed profile without revoking care roles', function (): void {
    $blocker = User::factory()->create(['name' => 'Safety Blocker']);
    $blocked = User::factory()->create(['name' => 'Safety Blocked']);
    $petManager = User::factory()->create();
    $blockerPet = PetProfile::factory()->for($blocker)->create(['name' => 'Blocker Pet']);
    $blockedPet = PetProfile::factory()->for($blocked)->create(['name' => 'Blocked Pet']);
    $blockedExpert = ExpertProfile::factory()->for($blocked, 'owner')->create([
        'public_name' => 'Blocked Expert',
    ]);
    $blockedGroup = ForumGroup::factory()->for($blocked, 'owner')->create([
        'name' => 'Blocked Group',
    ]);
    $managerRole = PetProfileManager::factory()
        ->for($blockedPet, 'profile')
        ->for($petManager)
        ->create();
    $resolver = app(SocialActorResolver::class);
    $blockerActor = safetyPublicActor($blocker);
    $blockedActor = safetyPublicActor($blocked);
    $blockerPetActor = $resolver->forPet($blockerPet);
    $blockedPetActor = $resolver->forPet($blockedPet);
    $blockedExpertActor = $resolver->forExpert($blockedExpert);
    $blockedGroupActor = $resolver->forGroup($blockedGroup);

    expect($resolver->controlledBy($petManager)->modelKeys())->toContain($blockedPetActor->id);

    $this->actingAs($blocker);
    $friendRequest = app(SendSocialRelationshipRequest::class)->handle(
        $blockerActor,
        $blockedActor,
        SocialRelationshipType::OwnerFriendship,
        'safety-friend-request',
    );
    $this->actingAs($blocked);
    app(RespondToSocialRelationshipRequest::class)->handle(
        $friendRequest,
        SocialRequestStatus::Accepted,
        'safety-friend-accept',
    );
    $this->actingAs($blocker);
    $petRequest = app(SendSocialRelationshipRequest::class)->handle(
        $blockerPetActor,
        $blockedPetActor,
        SocialRelationshipType::PetFriendship,
        'safety-pet-request',
    );

    $first = app(BlockSocialAccount::class)->handle(
        $blockerActor,
        $blockedActor,
        $blocked,
        'safety-account-block',
    );
    $replay = app(BlockSocialAccount::class)->handle(
        $blockerActor,
        $blockedActor,
        $blocked,
        'safety-account-block',
    );

    expect($replay->id)->toBe($first->id)
        ->and(SocialAccountBlock::query()->count())->toBe(1)
        ->and($friendRequest->fresh()->status)->toBe(SocialRequestStatus::Accepted)
        ->and($petRequest->fresh()->status)->toBe(SocialRequestStatus::Blocked)
        ->and(SocialRelationship::query()
            ->where('relationship_type', SocialRelationshipType::OwnerFriendship->value)
            ->firstOrFail()->status)->toBe(SocialRelationshipStatus::Ended)
        ->and($managerRole->fresh()->status)->toBe(PetManagerStatus::Active);

    $futurePet = PetProfile::factory()->for($blocked)->create(['name' => 'Future Hidden Pet']);
    $futurePetActor = $resolver->forPet($futurePet);
    $directory = app(SocialActorDirectory::class)->search(
        $blockerActor,
        $blocker,
        'Future Hidden',
    );

    expect($directory)->toBeEmpty();
    expect(fn () => app(SendSocialRelationshipRequest::class)->handle(
        $blockerPetActor,
        $futurePetActor,
        SocialRelationshipType::PetFriendship,
        'future-profile-blocked-request',
    ))->toThrow(ValidationException::class);
    expect(fn () => app(SendSocialRelationshipRequest::class)->handle(
        $blockerActor,
        $blockedExpertActor,
        SocialRelationshipType::Professional,
        'blocked-expert-request',
    ))->toThrow(ValidationException::class);
    expect(fn () => app(SendSocialRelationshipRequest::class)->handle(
        $blockerActor,
        $blockedGroupActor,
        SocialRelationshipType::GroupContext,
        'blocked-group-request',
    ))->toThrow(ValidationException::class);

    $this->actingAs($petManager);
    expect(fn () => app(RevokeSocialAccountBlock::class)->handle(
        $first,
        'unauthorized-account-unblock',
    ))->toThrow(AuthorizationException::class);

    $this->actingAs($blocker);
    app(RevokeSocialAccountBlock::class)->handle($first, 'safety-account-unblock');

    expect($first->fresh()->status)->toBe(SocialAccountBlockStatus::Revoked)
        ->and(SocialRelationship::query()
            ->where('relationship_type', SocialRelationshipType::OwnerFriendship->value)
            ->firstOrFail()->status)->toBe(SocialRelationshipStatus::Ended)
        ->and(app(SocialActorDirectory::class)->search(
            $blockerActor,
            $blocker,
            'Future Hidden',
        ))->toHaveCount(1);
});

it('reduces request throughput only after a meaningful low acceptance sample', function (): void {
    config()->set('social_relationships.request_limits.verified_hour', 20);
    config()->set('social_relationships.request_limits.verified_day', 20);
    config()->set('social_relationships.request_limits.new_hour', 20);
    config()->set('social_relationships.request_limits.new_day', 20);

    $sender = User::factory()->create();
    $recipients = User::factory()->count(4)->create();
    $resolver = app(SocialActorResolver::class);
    $source = safetyPublicActor($sender);

    foreach ($recipients->take(3) as $index => $recipient) {
        $target = safetyPublicActor($recipient);
        $this->actingAs($sender);
        $request = app(SendSocialRelationshipRequest::class)->handle(
            $source,
            $target,
            SocialRelationshipType::OwnerFriendship,
            "low-acceptance-request-{$index}",
        );
        $this->actingAs($recipient);
        app(RespondToSocialRelationshipRequest::class)->handle(
            $request,
            SocialRequestStatus::Declined,
            "low-acceptance-decline-{$index}",
        );
    }

    config()->set('social_relationships.request_limits.low_acceptance_minimum', 3);
    config()->set('social_relationships.request_limits.low_acceptance_floor', 0.5);
    config()->set('social_relationships.request_limits.low_acceptance_hour', 3);
    $this->actingAs($sender);

    expect(fn () => app(SendSocialRelationshipRequest::class)->handle(
        $source,
        safetyPublicActor($recipients->last()),
        SocialRelationshipType::OwnerFriendship,
        'low-acceptance-limited',
    ))->toThrow(ValidationException::class)
        ->and(SocialRelationshipRequest::query()
            ->where('created_by_user_id', $sender->id)
            ->count())->toBe(3);
});

it('applies request limits to the real account across pet profiles', function (): void {
    config()->set('social_relationships.request_limits.verified_hour', 2);
    config()->set('social_relationships.request_limits.verified_day', 2);
    config()->set('social_relationships.request_limits.new_hour', 2);
    config()->set('social_relationships.request_limits.new_day', 2);

    $sender = User::factory()->create();
    $resolver = app(SocialActorResolver::class);
    $sourceActors = collect(range(1, 3))->map(function (int $index) use ($resolver, $sender) {
        return $resolver->forPet(PetProfile::factory()->for($sender)->create([
            'name' => "Source {$index}",
        ]));
    });
    $targetActors = collect(range(1, 3))->map(function (int $index) use ($resolver) {
        $owner = User::factory()->create();

        return $resolver->forPet(PetProfile::factory()->for($owner)->create([
            'name' => "Target {$index}",
        ]));
    });
    $this->actingAs($sender);

    foreach ([0, 1] as $index) {
        app(SendSocialRelationshipRequest::class)->handle(
            $sourceActors[$index],
            $targetActors[$index],
            SocialRelationshipType::PetFriendship,
            "real-account-rate-{$index}",
        );
    }

    expect(fn () => app(SendSocialRelationshipRequest::class)->handle(
        $sourceActors[2],
        $targetActors[2],
        SocialRelationshipType::PetFriendship,
        'real-account-rate-blocked',
    ))->toThrow(ValidationException::class)
        ->and(SocialRelationshipRequest::query()
            ->where('created_by_user_id', $sender->id)
            ->count())->toBe(2);
});

it('prevents profile switching after a recipient stops repeat requests', function (): void {
    $sender = User::factory()->create();
    $recipient = User::factory()->create();
    $resolver = app(SocialActorResolver::class);
    $firstSource = $resolver->forPet(PetProfile::factory()->for($sender)->create());
    $secondSource = $resolver->forPet(PetProfile::factory()->for($sender)->create());
    $firstTarget = $resolver->forPet(PetProfile::factory()->for($recipient)->create());
    $secondTarget = $resolver->forPet(PetProfile::factory()->for($recipient)->create());
    $this->actingAs($sender);
    $request = app(SendSocialRelationshipRequest::class)->handle(
        $firstSource,
        $firstTarget,
        SocialRelationshipType::PetFriendship,
        'prevent-repeat-first',
    );

    $this->actingAs($recipient);
    app(RespondToSocialRelationshipRequest::class)->handle(
        $request,
        SocialRequestStatus::Declined,
        'prevent-repeat-decline',
        'recipient-prevented-repeats',
        true,
    );

    $this->actingAs($sender);
    expect(fn () => app(SendSocialRelationshipRequest::class)->handle(
        $secondSource,
        $secondTarget,
        SocialRelationshipType::PetFriendship,
        'prevent-repeat-second',
    ))->toThrow(ValidationException::class)
        ->and($request->fresh()->prevent_repeats)->toBeTrue();
});

it('normalizes request context and stops contact details and repeated templates', function (): void {
    config()->set('social_relationships.request_limits.verified_hour', 20);
    config()->set('social_relationships.request_limits.verified_day', 20);
    config()->set('social_relationships.request_limits.new_hour', 20);
    config()->set('social_relationships.request_limits.new_day', 20);
    config()->set('social_relationships.request_limits.duplicate_message_day', 2);

    $sender = User::factory()->create();
    $targets = User::factory()->count(4)->create();
    $resolver = app(SocialActorResolver::class);
    $source = safetyPublicActor($sender);
    $actors = $targets->map(fn (User $user) => safetyPublicActor($user));
    $this->actingAs($sender);
    $first = app(SendSocialRelationshipRequest::class)->handle(
        $source,
        $actors[0],
        SocialRelationshipType::OwnerFriendship,
        'template-first',
        '  We met   in the park.  ',
    );
    app(SendSocialRelationshipRequest::class)->handle(
        $source,
        $actors[1],
        SocialRelationshipType::OwnerFriendship,
        'template-second',
        'We met in the park.',
    );

    expect($first->message)->toBe('We met in the park.')
        ->and($first->getRawOriginal('message'))->not->toBe($first->message);
    expect(fn () => app(SendSocialRelationshipRequest::class)->handle(
        $source,
        $actors[2],
        SocialRelationshipType::OwnerFriendship,
        'template-third',
        'We met in the park.',
    ))->toThrow(ValidationException::class);
    expect(fn () => app(SendSocialRelationshipRequest::class)->handle(
        $source,
        $actors[3],
        SocialRelationshipType::OwnerFriendship,
        'contact-details-request',
        'Write to me at owner@example.test',
    ))->toThrow(ValidationException::class);
});

it('reports a request privately and can block its whole source account idempotently', function (): void {
    ForumReportReason::factory()->create([
        'stable_key' => 'unwanted-contact',
        'translation_key' => 'forum_moderation.reasons.unwanted-contact',
    ]);
    $sender = User::factory()->create();
    $recipient = User::factory()->create();
    $outsider = User::factory()->create();
    $resolver = app(SocialActorResolver::class);
    $source = safetyPublicActor($sender);
    $target = safetyPublicActor($recipient);
    $this->actingAs($sender);
    $request = app(SendSocialRelationshipRequest::class)->handle(
        $source,
        $target,
        SocialRelationshipType::OwnerFriendship,
        'reported-request',
        'We met at the city walk.',
    );

    $this->actingAs($recipient);
    $first = app(ReportSocialRelationshipRequest::class)->handle(
        request: $request,
        reasonKey: 'unwanted-contact',
        details: 'I do not know this account.',
        truthfulnessConfirmed: true,
        blockAccount: true,
        idempotencyKey: 'report-social-request',
    );
    $replay = app(ReportSocialRelationshipRequest::class)->handle(
        request: $request,
        reasonKey: 'unwanted-contact',
        details: 'I do not know this account.',
        truthfulnessConfirmed: true,
        blockAccount: true,
        idempotencyKey: 'report-social-request',
    );

    expect($replay->id)->toBe($first->id)
        ->and(ForumReport::query()->count())->toBe(1)
        ->and($first->subject_type)->toBe(SocialRelationshipRequest::class)
        ->and($first->subject_id)->toBe((string) $request->id)
        ->and($first->affected_user_id)->toBe($sender->id)
        ->and($first->toArray())->not->toHaveKey('details')
        ->and($request->fresh()->status)->toBe(SocialRequestStatus::RemovedAfterReport)
        ->and($request->fresh()->prevent_repeats)->toBeTrue()
        ->and(SocialAccountBlock::query()->count())->toBe(1)
        ->and(SocialRelationshipEvent::query()
            ->where('event_type', 'request-removed-after-report')
            ->count())->toBe(1);

    $this->actingAs($outsider);
    expect(fn () => app(ReportSocialRelationshipRequest::class)->handle(
        request: $request,
        reasonKey: 'unwanted-contact',
        details: null,
        truthfulnessConfirmed: true,
        blockAccount: false,
        idempotencyKey: 'outsider-report-social-request',
    ))->toThrow(AuthorizationException::class);
});

it('exposes safe request decisions and reporting through the livewire center', function (): void {
    ForumReportReason::factory()->create([
        'stable_key' => 'unwanted-contact',
        'translation_key' => 'forum_moderation.reasons.unwanted-contact',
    ]);
    $sender = User::factory()->create(['name' => 'Reported Sender']);
    $recipient = User::factory()->create();
    $resolver = app(SocialActorResolver::class);
    $source = safetyPublicActor($sender);
    $target = safetyPublicActor($recipient);
    $this->actingAs($sender);
    $request = app(SendSocialRelationshipRequest::class)->handle(
        $source,
        $target,
        SocialRelationshipType::OwnerFriendship,
        'livewire-report-request',
        'Context visible to the recipient.',
    );

    Livewire::actingAs($recipient)
        ->test(RelationshipCenter::class)
        ->assertSee('Reported Sender')
        ->assertSee('Context visible to the recipient.')
        ->assertSee('Accept request from Reported Sender')
        ->assertSee('Accept this request?')
        ->call('startReport', $request->request_key)
        ->set('reportForm.reason', 'unwanted-contact')
        ->set('reportForm.details', 'This request is not expected.')
        ->set('reportForm.truthfulnessConfirmed', true)
        ->set('reportForm.blockAccount', false)
        ->call('submitReport')
        ->assertHasNoErrors()
        ->assertDontSee('Context visible to the recipient.');

    expect(ForumReport::query()->count())->toBe(1)
        ->and(SocialAccountBlock::query()->count())->toBe(0)
        ->and($request->fresh()->status)->toBe(SocialRequestStatus::RemovedAfterReport);
});
