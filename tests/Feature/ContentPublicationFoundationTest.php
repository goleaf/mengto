<?php

declare(strict_types=1);

use App\Actions\CreateContentPublication;
use App\Data\CreateContentPublicationData;
use App\Enums\ContentAudienceActorEffect;
use App\Enums\ContentAudienceType;
use App\Enums\ContentDomainType;
use App\Enums\ContentPublicationEventType;
use App\Enums\ContentPublicationStatus;
use App\Enums\ContentPublicationType;
use App\Enums\PetManagerRole;
use App\Enums\PetProfilePermission;
use App\Enums\PetProfileVisibility;
use App\Enums\SocialRelationshipStatus;
use App\Enums\SocialRelationshipType;
use App\Models\ContentAudienceActor;
use App\Models\ContentAudienceRule;
use App\Models\ContentDomainLink;
use App\Models\ContentInteractionSetting;
use App\Models\ContentMediaAsset;
use App\Models\ContentPublication;
use App\Models\ContentPublicationEvent;
use App\Models\ForumGroup;
use App\Models\ForumGroupMembership;
use App\Models\PetProfile;
use App\Models\PetProfileManager;
use App\Models\PhotoAsset;
use App\Models\PhotoComment;
use App\Models\PhotoReaction;
use App\Models\Publication;
use App\Models\SocialAccountBlock;
use App\Models\SocialActor;
use App\Models\SocialRelationship;
use App\Models\User;
use App\Models\UserDomainState;
use App\Services\ContentChronologicalFeed;
use App\Services\ContentCompatibilityReport;
use App\Services\SocialActorResolver;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

function contentActorFor(User $user): SocialActor
{
    return app(SocialActorResolver::class)->forUser($user);
}

/**
 * @param  list<int>  $includedActorIds
 * @param  list<int>  $excludedActorIds
 */
function createContentFor(
    User $author,
    SocialActor $actor,
    ContentAudienceType $audience,
    array $includedActorIds = [],
    array $excludedActorIds = [],
    ?int $contextActorId = null,
    ?DateTimeInterface $expiresAt = null,
    ?string $body = null,
): ContentPublication {
    return app(CreateContentPublication::class)->handle(
        $author,
        $actor,
        new CreateContentPublicationData(
            type: ContentPublicationType::Post,
            status: ContentPublicationStatus::Published,
            audience: $audience,
            language: 'en',
            idempotencyKey: (string) Str::ulid(),
            title: 'A durable content publication',
            body: $body ?? 'A server-authorized publication body.',
            contextActorId: $contextActorId,
            includedActorIds: $includedActorIds,
            excludedActorIds: $excludedActorIds,
            expiresAt: $expiresAt,
        ),
    );
}

it('creates the indexed content foundation without replacing expert publications', function (): void {
    expect(Schema::hasColumns('content_publications', [
        'publication_key',
        'real_author_user_id',
        'publishing_actor_id',
        'representation_role',
        'content_type',
        'status',
        'creation_fingerprint',
        'idempotency_key',
        'published_at',
        'expires_at',
    ]))->toBeTrue()
        ->and(Schema::hasTable('content_audience_rules'))->toBeTrue()
        ->and(Schema::hasTable('content_audience_actors'))->toBeTrue()
        ->and(Schema::hasTable('content_interaction_settings'))->toBeTrue()
        ->and(Schema::hasTable('content_domain_links'))->toBeTrue()
        ->and(Schema::hasTable('content_media_assets'))->toBeTrue()
        ->and(Schema::hasTable('content_publication_media'))->toBeTrue()
        ->and(Schema::hasTable('content_publication_events'))->toBeTrue()
        ->and(Schema::hasTable((new Publication)->getTable()))->toBeTrue();

    $indexes = collect(Schema::getIndexes('content_publications'))->pluck('name');

    expect($indexes)->toContain('content_publications_chronological_idx')
        ->and($indexes)->toContain('content_publications_actor_chronological_idx')
        ->and($indexes)->toContain('content_publications_author_idempotency_unique');
});

it('rolls back only canonical content tables and preserves existing data', function (): void {
    $user = User::factory()->create();
    $expertPublication = Publication::factory()->create();
    $migrations = [
        require database_path('migrations/2026_08_01_000100_create_content_publications_table.php'),
        require database_path('migrations/2026_08_01_000110_create_content_audience_tables.php'),
        require database_path('migrations/2026_08_01_000120_create_content_interaction_settings_table.php'),
        require database_path('migrations/2026_08_01_000130_create_content_domain_links_table.php'),
        require database_path('migrations/2026_08_01_000140_create_content_media_tables.php'),
        require database_path('migrations/2026_08_01_000150_create_content_publication_events_table.php'),
    ];

    foreach (array_reverse($migrations) as $migration) {
        $migration->down();
    }

    expect(Schema::hasTable('content_publications'))->toBeFalse()
        ->and(Schema::hasTable('content_audience_rules'))->toBeFalse()
        ->and(Schema::hasTable('content_media_assets'))->toBeFalse()
        ->and(Schema::hasTable('content_publication_events'))->toBeFalse()
        ->and(Schema::hasTable((new Publication)->getTable()))->toBeTrue()
        ->and(User::query()->whereKey($user->id)->exists())->toBeTrue()
        ->and(Publication::query()->whereKey($expertPublication->id)->exists())->toBeTrue();

    foreach ($migrations as $migration) {
        $migration->up();
    }

    expect(Schema::hasTable('content_publications'))->toBeTrue()
        ->and(Schema::hasTable('content_audience_rules'))->toBeTrue()
        ->and(Schema::hasTable('content_media_assets'))->toBeTrue()
        ->and(Schema::hasTable('content_publication_events'))->toBeTrue()
        ->and(Publication::query()->whereKey($expertPublication->id)->exists())->toBeTrue();
});

it('creates one attributed aggregate and one immutable event for a replayed command', function (): void {
    $author = $this->authenticatedUser;
    $actor = contentActorFor($author);
    $key = (string) Str::ulid();
    $data = new CreateContentPublicationData(
        type: ContentPublicationType::Photo,
        status: ContentPublicationStatus::Published,
        audience: ContentAudienceType::Registered,
        language: 'lt',
        idempotencyKey: $key,
        title: 'Baksas parke',
        body: 'Nuotrauka be tikslios vietos.',
        allowReposts: false,
        allowMediaDownloads: false,
    );
    $action = app(CreateContentPublication::class);

    $first = $action->handle($author, $actor, $data);
    $second = $action->handle($author, $actor, $data);

    expect($second->id)->toBe($first->id)
        ->and(ContentPublication::query()->count())->toBe(1)
        ->and(ContentAudienceRule::query()->count())->toBe(1)
        ->and(ContentInteractionSetting::query()->count())->toBe(1)
        ->and(ContentPublicationEvent::query()->count())->toBe(1)
        ->and($first->real_author_user_id)->toBe($author->id)
        ->and($first->publishing_actor_id)->toBe($actor->id)
        ->and($first->representation_role)->toBe('self')
        ->and($first->audienceRule->audience_type)->toBe(ContentAudienceType::Registered)
        ->and($first->events->first()->event_type)->toBe(ContentPublicationEventType::Published)
        ->and($first->events->first()->representation_role)->toBe('self')
        ->and($first->interactionSettings->allow_reposts)->toBeFalse()
        ->and($first->interactionSettings->allow_media_downloads)->toBeFalse();

    expect(fn () => $action->handle(
        $author,
        $actor,
        new CreateContentPublicationData(
            type: ContentPublicationType::Photo,
            status: ContentPublicationStatus::Published,
            audience: ContentAudienceType::Everyone,
            language: 'lt',
            idempotencyKey: $key,
            title: 'A different command using the same key',
        ),
    ))->toThrow(InvalidArgumentException::class, 'different publication command');

    $event = $first->events->firstOrFail();

    expect(fn () => $event->update(['metadata' => ['rewritten' => true]]))
        ->toThrow(LogicException::class, 'append-only')
        ->and(fn () => $event->delete())
        ->toThrow(LogicException::class, 'append-only');
});

it('records the real author and their role when publishing for a pet profile', function (): void {
    $petOwner = User::factory()->create();
    $pet = PetProfile::factory()->for($petOwner)->create(['name' => 'Baksas']);
    PetProfileManager::factory()
        ->for($pet, 'profile')
        ->for($this->authenticatedUser)
        ->create(['role' => PetManagerRole::CoOwner]);
    $petActor = app(SocialActorResolver::class)->forPet($pet);

    $publication = createContentFor(
        $this->authenticatedUser,
        $petActor,
        ContentAudienceType::Registered,
    );

    expect($publication->real_author_user_id)->toBe($this->authenticatedUser->id)
        ->and($publication->publishing_actor_id)->toBe($petActor->id)
        ->and($publication->representation_role)->toBe(PetManagerRole::CoOwner->value)
        ->and($publication->events->first()->actor_user_id)->toBe($this->authenticatedUser->id)
        ->and($publication->events->first()->represented_actor_id)->toBe($petActor->id)
        ->and($publication->events->first()->representation_role)->toBe(PetManagerRole::CoOwner->value);
});

it('rejects false representation and public posts from a hidden profile', function (): void {
    $other = User::factory()->create();
    $otherActor = contentActorFor($other);
    $data = new CreateContentPublicationData(
        type: ContentPublicationType::Post,
        status: ContentPublicationStatus::Published,
        audience: ContentAudienceType::Everyone,
        language: 'en',
        idempotencyKey: (string) Str::ulid(),
        body: 'Not authorized.',
    );

    expect(fn () => app(CreateContentPublication::class)->handle(
        $this->authenticatedUser,
        $otherActor,
        $data,
    ))->toThrow(AuthorizationException::class);

    $ownActor = contentActorFor($this->authenticatedUser);
    $ownActor->forceFill(['is_discoverable' => false])->save();

    expect(fn () => app(CreateContentPublication::class)->handle(
        $this->authenticatedUser,
        $ownActor,
        new CreateContentPublicationData(
            type: ContentPublicationType::Post,
            status: ContentPublicationStatus::Published,
            audience: ContentAudienceType::Everyone,
            language: 'en',
            idempotencyKey: (string) Str::ulid(),
            body: 'This would widen a hidden profile.',
        ),
    ))->toThrow(InvalidArgumentException::class);
});

it('keeps broad audiences within profile privacy while allowing current friends', function (): void {
    $petOwner = User::factory()->create();
    $pet = PetProfile::factory()
        ->for($petOwner)
        ->privateProfile()
        ->create();
    $petActor = app(SocialActorResolver::class)->forPet($pet);

    expect(PetProfileVisibility::fromStored($pet->visibility))->toBe(PetProfileVisibility::Private)
        ->and(fn () => createContentFor(
            $petOwner,
            $petActor,
            ContentAudienceType::Everyone,
        ))->toThrow(InvalidArgumentException::class)
        ->and(fn () => createContentFor(
            $petOwner,
            $petActor,
            ContentAudienceType::Registered,
        ))->toThrow(InvalidArgumentException::class);

    $friendsPost = createContentFor(
        $petOwner,
        $petActor,
        ContentAudienceType::Friends,
    );
    $viewerActor = contentActorFor($this->authenticatedUser);
    SocialRelationship::factory()->create([
        'source_actor_id' => $petActor->id,
        'target_actor_id' => $viewerActor->id,
        'relationship_type' => SocialRelationshipType::PetFriendship,
        'direction' => SocialRelationshipType::PetFriendship->direction(),
        'created_by_user_id' => $petOwner->id,
    ]);

    $this->get(route('content.show', $friendsPost))->assertSuccessful();
});

it('requires explicit publish permission for a managed pet profile', function (): void {
    $petOwner = User::factory()->create();
    $pet = PetProfile::factory()->for($petOwner)->create();
    PetProfileManager::factory()
        ->for($pet, 'profile')
        ->for($this->authenticatedUser)
        ->create([
            'role' => PetManagerRole::CoOwner,
            'permission_overrides' => [
                'deny' => [PetProfilePermission::Publish->value],
            ],
        ]);
    $petActor = app(SocialActorResolver::class)->forPet($pet);

    expect(fn () => createContentFor(
        $this->authenticatedUser,
        $petActor,
        ContentAudienceType::Registered,
    ))->toThrow(AuthorizationException::class);
});

it('enforces guest registered selected and excluded audiences on direct urls', function (): void {
    $author = User::factory()->create();
    $authorActor = contentActorFor($author);
    $viewerActor = contentActorFor($this->authenticatedUser);
    $public = createContentFor($author, $authorActor, ContentAudienceType::Everyone);
    $registered = createContentFor($author, $authorActor, ContentAudienceType::Registered);
    $selected = createContentFor(
        $author,
        $authorActor,
        ContentAudienceType::Selected,
        [$viewerActor->id],
    );
    $excluded = createContentFor(
        $author,
        $authorActor,
        ContentAudienceType::Registered,
        excludedActorIds: [$viewerActor->id],
    );

    Auth::logout();
    $this->get(route('content.show', $public))->assertSuccessful();
    $this->get(route('content.show', $registered))->assertForbidden();
    $this->get(route('content.show', $selected))->assertForbidden();

    $this->actingAs($this->authenticatedUser);
    $this->get(route('content.show', $registered))->assertSuccessful();
    $this->get(route('content.show', $selected))->assertSuccessful();
    $this->get(route('content.show', $excluded))->assertForbidden();
});

it('evaluates current followers friends close circle family and group membership at read time', function (): void {
    $author = User::factory()->create();
    $authorActor = contentActorFor($author);
    $viewerActor = contentActorFor($this->authenticatedUser);
    $followerPost = createContentFor($author, $authorActor, ContentAudienceType::Followers);
    $friendPost = createContentFor($author, $authorActor, ContentAudienceType::Friends);
    $closeCirclePost = createContentFor($author, $authorActor, ContentAudienceType::CloseCircle);
    $familyPost = createContentFor($author, $authorActor, ContentAudienceType::Family);

    $this->get(route('content.show', $followerPost))->assertForbidden();
    $this->get(route('content.show', $friendPost))->assertForbidden();
    $this->get(route('content.show', $closeCirclePost))->assertForbidden();
    $this->get(route('content.show', $familyPost))->assertForbidden();

    SocialRelationship::factory()->create([
        'source_actor_id' => $viewerActor->id,
        'target_actor_id' => $authorActor->id,
        'relationship_type' => SocialRelationshipType::Follow,
        'direction' => SocialRelationshipType::Follow->direction(),
        'created_by_user_id' => $this->authenticatedUser->id,
    ]);
    $friendship = SocialRelationship::factory()->create([
        'source_actor_id' => $authorActor->id,
        'target_actor_id' => $viewerActor->id,
        'relationship_type' => SocialRelationshipType::OwnerFriendship,
        'direction' => SocialRelationshipType::OwnerFriendship->direction(),
        'created_by_user_id' => $author->id,
    ]);
    $closeCircle = SocialRelationship::factory()->create([
        'source_actor_id' => $authorActor->id,
        'target_actor_id' => $viewerActor->id,
        'relationship_type' => SocialRelationshipType::CloseCircle,
        'direction' => SocialRelationshipType::CloseCircle->direction(),
        'created_by_user_id' => $author->id,
    ]);
    SocialRelationship::factory()->create([
        'source_actor_id' => $viewerActor->id,
        'target_actor_id' => $authorActor->id,
        'relationship_type' => SocialRelationshipType::Family,
        'direction' => SocialRelationshipType::Family->direction(),
        'created_by_user_id' => $this->authenticatedUser->id,
    ]);

    $this->get(route('content.show', $followerPost))->assertSuccessful();
    $this->get(route('content.show', $friendPost))->assertSuccessful();
    $this->get(route('content.show', $closeCirclePost))->assertSuccessful();
    $this->get(route('content.show', $familyPost))->assertSuccessful();

    $friendship->forceFill([
        'status' => SocialRelationshipStatus::Ended,
        'active_key' => null,
        'ended_at' => now(),
    ])->save();
    $this->get(route('content.show', $friendPost))->assertForbidden();

    $closeCircle->forceFill([
        'status' => SocialRelationshipStatus::Ended,
        'active_key' => null,
        'ended_at' => now(),
    ])->save();
    $this->get(route('content.show', $closeCirclePost))->assertForbidden();

    $group = ForumGroup::factory()->for($author, 'owner')->create();
    $groupActor = SocialActor::factory()->forGroup($group)->create();
    $groupPost = createContentFor(
        $author,
        $authorActor,
        ContentAudienceType::Group,
        contextActorId: $groupActor->id,
    );

    $this->get(route('content.show', $groupPost))->assertForbidden();
    ForumGroupMembership::factory()->for($group, 'group')->for($this->authenticatedUser)->create();
    $this->get(route('content.show', $groupPost))->assertSuccessful();
});

it('fails closed for audience credentials that do not yet have a verifier', function (): void {
    $author = User::factory()->create();
    $authorActor = contentActorFor($author);
    $eventPost = createContentFor(
        $author,
        $authorActor,
        ContentAudienceType::EventParticipants,
    );
    $temporaryPost = createContentFor(
        $author,
        $authorActor,
        ContentAudienceType::TemporaryLink,
    );
    $authorOnlyPost = createContentFor(
        $author,
        $authorActor,
        ContentAudienceType::AuthorOnly,
    );

    $this->get(route('content.show', $eventPost))->assertForbidden();
    $this->get(route('content.show', $temporaryPost))->assertForbidden();
    $this->get(route('content.show', $authorOnlyPost))->assertForbidden();

    $this->actingAs($author);
    $this->get(route('content.show', $authorOnlyPost))->assertSuccessful();
});

it('removes blocked expired and undiscoverable content from direct urls and feeds', function (): void {
    $author = User::factory()->create();
    $authorActor = contentActorFor($author);
    $visible = createContentFor($author, $authorActor, ContentAudienceType::Everyone);
    $expired = createContentFor(
        $author,
        $authorActor,
        ContentAudienceType::Everyone,
        expiresAt: now()->subSecond(),
    );

    $this->get(route('content.show', $visible))->assertSuccessful();
    $this->get(route('content.show', $expired))->assertForbidden();

    SocialAccountBlock::factory()->create([
        'blocker_user_id' => $this->authenticatedUser->id,
        'blocked_user_id' => $author->id,
        'created_by_user_id' => $this->authenticatedUser->id,
    ]);

    $this->get(route('content.show', $visible))->assertForbidden();
    $this->get(route('content.index'))
        ->assertSuccessful()
        ->assertDontSee('A server-authorized publication body.');

    SocialAccountBlock::query()->delete();
    $authorActor->forceFill(['is_discoverable' => false])->save();
    $this->get(route('content.show', $visible))->assertForbidden();
});

it('renders a bounded chronological projection without per-publication queries', function (): void {
    $author = User::factory()->create();
    $actor = contentActorFor($author);

    foreach (range(1, 20) as $index) {
        createContentFor(
            $author,
            $actor,
            ContentAudienceType::Everyone,
            body: "Bounded publication {$index}",
        );
    }

    DB::flushQueryLog();
    DB::enableQueryLog();
    $page = app(ContentChronologicalFeed::class)->page($this->authenticatedUser, 15);
    $queryCount = count(DB::getQueryLog());
    DB::disableQueryLog();

    expect($page['items'])->toHaveCount(15)
        ->and($queryCount)->toBeLessThanOrEqual(11)
        ->and($page['next_url'])->not->toBeNull();
});

it('provides factories for every content foundation model', function (): void {
    $publication = ContentPublication::factory()->create();
    $rule = ContentAudienceRule::factory()->for($publication, 'publication')->create();
    ContentAudienceActor::factory()->for($rule, 'rule')->create();
    ContentInteractionSetting::factory()->for($publication, 'publication')->create();
    ContentDomainLink::factory()->for($publication, 'publication')->create();
    ContentMediaAsset::factory()->create();
    ContentPublicationEvent::factory()->for($publication, 'publication')->create();

    expect(ContentPublication::query()->count())->toBeGreaterThanOrEqual(1)
        ->and(ContentAudienceRule::query()->count())->toBe(1)
        ->and(ContentAudienceActor::query()->count())->toBe(1)
        ->and(ContentInteractionSetting::query()->count())->toBe(1)
        ->and(ContentDomainLink::query()->count())->toBe(1)
        ->and(ContentMediaAsset::query()->count())->toBe(1)
        ->and(ContentPublicationEvent::query()->count())->toBe(1);
});

it('links independent media and typed domains without exposing private storage paths', function (): void {
    $author = User::factory()->create();
    $actor = contentActorFor($author);
    $publication = createContentFor($author, $actor, ContentAudienceType::Everyone);
    $media = ContentMediaAsset::factory()->create([
        'owner_user_id' => $author->id,
        'created_by_user_id' => $author->id,
        'path' => 'content/private/baks-original.jpg',
        'checksum_sha256' => hash('sha256', 'private-baks-original'),
    ]);
    $publication->mediaAssets()->attach($media->id, [
        'position' => 1,
        'is_cover' => true,
        'caption' => 'Baksas in the park',
    ]);
    $publication->domainLinks()->create([
        'domain_type' => ContentDomainType::Pet,
        'domain_key' => 'pet-baksas',
        'relationship' => 'subject',
        'is_primary' => true,
    ]);

    expect($publication->mediaAssets()->pluck('content_media_assets.id')->all())->toBe([$media->id])
        ->and($media->publications()->pluck('content_publications.id')->all())->toBe([$publication->id])
        ->and($publication->domainLinks()->value('domain_type'))->toBe(ContentDomainType::Pet);

    $this->get(route('content.show', $publication))
        ->assertSuccessful()
        ->assertDontSee('content/private/baks-original.jpg')
        ->assertDontSee($media->checksum_sha256);
});

it('reports preserved content compatibility without importing private payloads', function (): void {
    $legacyUser = User::factory()->create();
    $legacyState = UserDomainState::factory()->for($legacyUser)->create([
        'namespace' => 'prototype.state.v1',
        'payload' => ['posts' => [['key' => 'private-prototype-post']]],
    ]);
    $photo = PhotoAsset::factory()->create();
    PhotoComment::factory()->for($photo, 'photoAsset')->create();
    PhotoReaction::factory()->for($photo, 'photoAsset')->create();
    Publication::factory()->create();

    $report = app(ContentCompatibilityReport::class)->generate();

    $this->artisan('content:compatibility-report', ['--json' => true])
        ->expectsOutputToContain('"canonical_publications":')
        ->assertSuccessful();

    expect($report['expert_publications'])->toBe(Publication::query()->count())
        ->and($report['legacy_photo_assets'])->toBe(PhotoAsset::query()->count())
        ->and($report['legacy_photo_comments'])->toBe(PhotoComment::query()->count())
        ->and($report['legacy_photo_reactions'])->toBe(PhotoReaction::query()->count())
        ->and($report['encrypted_prototype_state_rows'])->toBe(1)
        ->and($legacyState->fresh()->payload)->toBe([
            'posts' => [['key' => 'private-prototype-post']],
        ])->and(ContentPublication::query()->count())->toBe(0);
});

it('stores inclusion and exclusion as independent audience records', function (): void {
    $author = User::factory()->create();
    $actor = contentActorFor($author);
    $included = contentActorFor(User::factory()->create());
    $excluded = contentActorFor(User::factory()->create());
    $publication = createContentFor(
        $author,
        $actor,
        ContentAudienceType::Selected,
        [$included->id],
        [$excluded->id],
    );

    expect($publication->audienceRule->actors()->where(
        'effect',
        ContentAudienceActorEffect::Include->value,
    )->pluck('social_actor_id')->all())->toBe([$included->id])
        ->and($publication->audienceRule->actors()->where(
            'effect',
            ContentAudienceActorEffect::Exclude->value,
        )->pluck('social_actor_id')->all())->toBe([$excluded->id]);
});
