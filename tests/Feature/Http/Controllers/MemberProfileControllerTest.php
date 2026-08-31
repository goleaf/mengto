<?php

declare(strict_types=1);

use App\Enums\ContentAudienceType;
use App\Enums\SocialRelationshipType;
use App\Models\ContentAudienceRule;
use App\Models\ContentInteractionSetting;
use App\Models\ContentPublication;
use App\Models\PetProfile;
use App\Models\SocialAccountBlock;
use App\Models\SocialRelationship;
use App\Models\User;
use App\Services\SocialActorResolver;

test('owner profile renders canonical facts private pets and honest aggregate counts', function (): void {
    $owner = User::factory()->onboarded()->create([
        'name' => 'Andrej Prus',
        'email' => 'andrej-profile@example.test',
        'created_at' => '2023-01-15 10:00:00',
        'locale' => 'en',
    ]);
    $actor = $owner->socialActor()->firstOrFail();
    $publicPet = PetProfile::factory()->for($owner)->create(['name' => 'Andrej Public Pet']);
    $privatePet = PetProfile::factory()->for($owner)->privateProfile()->create([
        'name' => 'Andrej Private Pet',
    ]);
    $otherPet = PetProfile::factory()->create(['name' => 'Other Account Pet']);
    $publication = ContentPublication::factory()
        ->by($owner, $actor)
        ->published()
        ->create(['title' => 'Andrej private routine']);
    ContentAudienceRule::factory()->for($publication, 'publication')->create([
        'audience_type' => ContentAudienceType::AuthorOnly,
    ]);
    ContentInteractionSetting::factory()->for($publication, 'publication')->create();
    $this->actingAs($owner);

    $response = $this->get(route('members.show', $actor))->assertOk();

    $response
        ->assertSee('Andrej Prus')
        ->assertSee('January 2023')
        ->assertSee(__('member_profiles.details.email_verified'))
        ->assertSee($publicPet->name)
        ->assertSee($privatePet->name)
        ->assertSee($publication->title)
        ->assertSee('data-member-stat="pets"', false)
        ->assertSee('data-member-stat="posts"', false)
        ->assertDontSee($otherPet->name)
        ->assertDontSee('Mia Carter')
        ->assertDontSee('Scout')
        ->assertDontSee('Nori')
        ->assertDontSee('2.4k');

    $xpath = responseXPath($response);

    expect($xpath->evaluate('string(//*[@data-member-stat="pets"]/@data-value)'))->toBe('2')
        ->and($xpath->evaluate('string(//*[@data-member-stat="posts"]/@data-value)'))->toBe('1')
        ->and($xpath->evaluate('string(//*[@data-header-link="profile"]/@href)'))
        ->toBe(route('members.show', $actor))
        ->and($xpath->evaluate('string(//*[@data-header-link="profile"]/@aria-current)'))
        ->toBe('page');
});

test('canonical member profile localizes labels without translating the persons identity', function (
    string $locale,
): void {
    $owner = User::factory()->onboarded()->create([
        'name' => 'Andrej Prus',
        'email' => "andrej-member-{$locale}@example.test",
        'locale' => $locale,
    ]);
    $actor = $owner->socialActor()->firstOrFail();
    $this->actingAs($owner);

    $response = $this->get(route('members.show', $actor))->assertOk();
    $xpath = responseXPath($response);

    expect($xpath->evaluate('string(//main//h1)'))->toBe('Andrej Prus')
        ->and($xpath->evaluate('string(//html/@lang)'))->toBe($locale)
        ->and($response->getContent())->toContain(trans('member_profiles.details.title', locale: $locale))
        ->and($response->getContent())->toContain(trans('member_profiles.page.private_status', locale: $locale));

    if ($locale !== 'en') {
        expect(trans('member_profiles.details.title', locale: $locale))
            ->not->toBe(trans('member_profiles.details.title', locale: 'en'))
            ->and(trans('member_profiles.page.private_status', locale: $locale))
            ->not->toBe(trans('member_profiles.page.private_status', locale: 'en'));
    }
})->with(['en', 'lt', 'ru']);

test('unrelated viewers see only public profile records', function (): void {
    $member = User::factory()->create(['name' => 'Visible Member']);
    $actor = app(SocialActorResolver::class)->forUser($member);
    $actor->forceFill(['is_discoverable' => true])->saveOrFail();
    $publicPet = PetProfile::factory()->for($member)->create(['name' => 'Visible Member Pet']);
    $privatePet = PetProfile::factory()->for($member)->privateProfile()->create([
        'name' => 'Hidden Member Pet',
    ]);
    $viewer = User::factory()->onboarded()->create();
    $this->actingAs($viewer);

    $this->get(route('members.show', $actor))
        ->assertOk()
        ->assertSee($member->name)
        ->assertSee($publicPet->name)
        ->assertDontSee($privatePet->name);
});

test('private member profiles are indistinguishable from missing profiles to another user', function (): void {
    $member = User::factory()->onboarded()->create();
    $actor = $member->socialActor()->firstOrFail();
    $viewer = User::factory()->onboarded()->create();
    $this->actingAs($viewer);

    $this->get(route('members.show', $actor))->assertNotFound();
});

test('account blocks in either direction hide the member profile', function (bool $viewerBlocks): void {
    $member = User::factory()->create();
    $actor = app(SocialActorResolver::class)->forUser($member);
    $actor->forceFill(['is_discoverable' => true])->saveOrFail();
    $viewer = User::factory()->onboarded()->create();
    $this->actingAs($viewer);

    $this->get(route('members.show', $actor))->assertOk();

    SocialAccountBlock::factory()->create([
        'blocker_user_id' => $viewerBlocks ? $viewer->id : $member->id,
        'blocked_user_id' => $viewerBlocks ? $member->id : $viewer->id,
        'created_by_user_id' => $viewerBlocks ? $viewer->id : $member->id,
    ]);

    $this->get(route('members.show', $actor))->assertNotFound();
})->with([
    'viewer blocks member' => [true],
    'member blocks viewer' => [false],
]);

test('member profile authorization uses exact block checks beyond projection limits', function (
    string $limit,
): void {
    $member = User::factory()->create();
    $memberActor = app(SocialActorResolver::class)->forUser($member);
    $memberActor->forceFill(['is_discoverable' => true])->saveOrFail();
    $viewer = User::factory()->onboarded()->create();
    $viewerActor = $viewer->socialActor()->firstOrFail();
    $unrelated = User::factory()->onboarded()->create();
    $unrelatedActor = $unrelated->socialActor()->firstOrFail();
    $this->actingAs($viewer);

    config()->set("social_relationships.{$limit}", 1);

    $this->get(route('members.show', $memberActor))->assertOk();

    if ($limit === 'account_block_limit') {
        SocialAccountBlock::factory()->create([
            'blocker_user_id' => $viewer->id,
            'blocked_user_id' => $unrelated->id,
            'created_by_user_id' => $viewer->id,
        ]);
        SocialAccountBlock::factory()->create([
            'blocker_user_id' => $viewer->id,
            'blocked_user_id' => $member->id,
            'created_by_user_id' => $viewer->id,
        ]);
    } else {
        SocialRelationship::factory()->create([
            'source_actor_id' => $viewerActor->id,
            'target_actor_id' => $unrelatedActor->id,
            'relationship_type' => SocialRelationshipType::Block,
            'created_by_user_id' => $viewer->id,
        ]);
        SocialRelationship::factory()->create([
            'source_actor_id' => $viewerActor->id,
            'target_actor_id' => $memberActor->id,
            'relationship_type' => SocialRelationshipType::Block,
            'created_by_user_id' => $viewer->id,
        ]);
    }

    $this->get(route('members.show', $memberActor))->assertNotFound();
})->with(['account_block_limit', 'relationship_limit']);

test('a reverse actor block hides a previously visible member profile', function (): void {
    $member = User::factory()->create(['name' => 'Reverse Block Member']);
    $memberActor = app(SocialActorResolver::class)->forUser($member);
    $memberActor->forceFill(['is_discoverable' => true])->saveOrFail();
    $viewer = User::factory()->onboarded()->create();
    $viewerActor = $viewer->socialActor()->firstOrFail();
    $this->actingAs($viewer);

    $this->get(route('members.show', $memberActor))->assertOk();

    SocialRelationship::factory()->create([
        'source_actor_id' => $memberActor->id,
        'target_actor_id' => $viewerActor->id,
        'relationship_type' => SocialRelationshipType::Block,
        'created_by_user_id' => $member->id,
    ]);

    $this->get(route('members.show', $memberActor))->assertNotFound();
});

test('viewing another actor never replaces the authenticated self identity', function (): void {
    $alice = User::factory()->onboarded()->create(['name' => 'Alice Example']);
    $bob = User::factory()->onboarded()->create(['name' => 'Bob Example']);
    $aliceActor = $alice->socialActor()->firstOrFail();
    $bobActor = $bob->socialActor()->firstOrFail();
    $bobActor->forceFill(['is_discoverable' => true])->saveOrFail();
    $this->actingAs($alice);

    $response = $this->get(route('members.show', $bobActor))->assertOk();
    $xpath = responseXPath($response);

    expect($xpath->evaluate('string(//main//h1)'))->toBe('Bob Example')
        ->and(trim((string) $xpath->evaluate(
            'string(//*[@data-header-link="profile"]/*[contains(@class, "header-profile__name")])',
        )))->toBe('Alice Example')
        ->and($xpath->evaluate('string(//*[@data-header-link="profile"]/@href)'))
        ->toBe(route('members.show', $aliceActor));
});

test('target email verification visibility follows the configured policy', function (bool $enabled, int $status): void {
    config()->set('platform.email_verification_enabled', $enabled);
    $member = User::factory()->unverified()->create(['name' => 'Pending Member']);
    $actor = app(SocialActorResolver::class)->forUser($member);
    $actor->forceFill(['is_discoverable' => true])->saveOrFail();
    $viewer = User::factory()->onboarded()->create();
    $this->actingAs($viewer);

    $this->get(route('members.show', $actor))->assertStatus($status);
})->with([
    'verification enabled' => [true, 404],
    'verification disabled' => [false, 200],
]);
