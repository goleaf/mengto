<?php

declare(strict_types=1);

use App\Enums\ContentAudienceType;
use App\Models\ContentAudienceRule;
use App\Models\ContentInteractionSetting;
use App\Models\ContentPublication;
use App\Models\PetProfile;
use App\Models\SocialAccountBlock;
use App\Models\User;

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

test('unrelated viewers see only public profile records', function (): void {
    $member = User::factory()->create(['name' => 'Visible Member']);
    $actor = app(App\Services\SocialActorResolver::class)->forUser($member);
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
    $actor = app(App\Services\SocialActorResolver::class)->forUser($member);
    $viewer = User::factory()->onboarded()->create();
    $this->actingAs($viewer);

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

