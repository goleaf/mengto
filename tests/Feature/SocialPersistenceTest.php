<?php

declare(strict_types=1);

use App\Actions\UpdatePetProfile;
use App\Actions\UpdatePetProfilePrivacy;
use App\Models\AuditLog;
use App\Models\PetProfile;
use App\Models\User;
use App\Models\UserDomainState;
use App\Policies\PetProfilePolicy;
use App\Services\EventState;
use App\Services\GroupState;
use App\Services\MessageState;
use App\Services\PersistentStateStore;
use App\Services\PetFriendState;
use App\Services\PetProfileCatalog;
use App\Services\PlaceState;
use App\Services\PrototypeState;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;

test('social mutations persist across sessions and remain isolated by user', function () {
    app(PrototypeState::class)->toggle('saved', 'post-scout');
    app(GroupState::class)->join('quiet-walks', 'public');
    app(EventState::class)->toggleInterest('calm-park-event');
    app(MessageState::class)->toggleConversation('ari', 'muted');
    app(PlaceState::class)->toggleSaved('vingis-quiet-loop');
    app(PetFriendState::class)->dismissRecommendation('pet-scout', 'pet-coco-spaniel');

    expect(UserDomainState::query()->where('user_id', $this->authenticatedUser->id)->count())
        ->toBe(6);

    Session::flush();

    expect(app(PrototypeState::class)->isActive('saved', 'post-scout'))->toBeTrue()
        ->and(app(GroupState::class)->membership('quiet-walks'))->toBe('joined')
        ->and(app(EventState::class)->isInterested('calm-park-event'))->toBeTrue()
        ->and(app(MessageState::class)->conversation('ari')['muted'])->toBeTrue()
        ->and(app(PlaceState::class)->isSaved('vingis-quiet-loop'))->toBeTrue()
        ->and(app(PetFriendState::class)->recommendationIsDismissed(
            'pet-scout',
            'pet-coco-spaniel',
        ))->toBeTrue();

    $other = User::factory()->create();
    $this->actingAs($other);

    expect(app(PrototypeState::class)->isActive('saved', 'post-scout'))->toBeFalse()
        ->and(app(PlaceState::class)->isSaved('vingis-quiet-loop'))->toBeFalse();
});

test('the shared action endpoint dispatches extracted social domain handlers', function () {
    $this->post(route('actions.perform'), [
        'action' => 'toggle-event-interest',
        'target' => 'small-dog-social',
    ])->assertRedirect();

    $this->post(route('actions.perform'), [
        'action' => 'toggle-subscription',
        'target' => 'owner-priya-shah',
    ])->assertRedirect();

    $this->post(route('actions.perform'), [
        'action' => 'dismiss-pet-friend-recommendation',
        'source_pet' => 'pet-scout',
        'target' => 'pet-coco-spaniel',
    ])->assertRedirect(route('pet-friends.index', [
        'pet' => 'scout',
        'tab' => 'discover',
    ]));

    $this->post(route('actions.perform'), [
        'action' => 'join-group',
        'target' => 'portland-labradors',
    ])->assertRedirect(route('groups.show', [
        'group' => 'portland-labradors',
        'tab' => 'overview',
    ]));

    expect(app(EventState::class)->isInterested('small-dog-social'))->toBeTrue()
        ->and(app(PrototypeState::class)->isSubscribed('owner-priya-shah'))->toBeTrue()
        ->and(app(PetFriendState::class)->recommendationIsDismissed(
            'pet-scout',
            'pet-coco-spaniel',
        ))->toBeTrue()
        ->and(app(GroupState::class)->membership('portland-labradors'))->toBe('joined')
        ->and(UserDomainState::query()
            ->where('user_id', $this->authenticatedUser->id)
            ->whereIn('namespace', [
                'events.state.v1',
                'prototype.state.v1',
                'pet-friends.state.v1',
                'groups.state.v1',
            ])
            ->count())->toBe(4);
});

test('dynamic social action feedback follows the active locale', function () {
    $this->authenticatedUser->forceFill(['locale' => 'ru'])->save();

    $this->post(route('actions.perform'), [
        'action' => 'toggle-subscription',
        'target' => 'owner-priya-shah',
    ])
        ->assertRedirect()
        ->assertSessionHas('feedback', 'Вы подписались на Priya Shah.');
});

test('pet profiles are durable separate identities with owner-scoped policy decisions', function () {
    $profile = PetProfile::factory()
        ->for($this->authenticatedUser)
        ->create([
            'profile_key' => 'pet-scout',
            'slug' => 'scout',
            'name' => 'Scout Persisted',
            'species' => 'Dog',
            'breed' => 'Collie mix',
            'profile_data' => ['status' => 'Ready for a calm walk'],
        ]);
    $other = User::factory()->create();
    $blocked = User::factory()->blocked()->create();
    $policy = app(PetProfilePolicy::class);
    $presented = app(PetProfileCatalog::class)->find('scout');

    expect($presented)
        ->not->toBeNull()
        ->and($presented['name'])->toBe('Scout Persisted')
        ->and($presented['status'])->toBe('Ready for a calm walk')
        ->and($policy->view(null, $profile))->toBeTrue()
        ->and($policy->view($other, $profile))->toBeTrue()
        ->and($policy->update($this->authenticatedUser, $profile))->toBeTrue()
        ->and($policy->update($other, $profile))->toBeFalse()
        ->and($policy->update($blocked, $profile))->toBeFalse();

    $profile->forceFill(['visibility' => 'private'])->save();

    expect($policy->view(null, $profile))->toBeFalse()
        ->and($policy->view($this->authenticatedUser, $profile))->toBeTrue()
        ->and($policy->view($other, $profile))->toBeFalse();
});

test('concurrent social state snapshots reject stale overwrites', function () {
    $first = app(PersistentStateStore::class);
    $second = app(PersistentStateStore::class);

    expect($first->get('concurrency.test', ['value' => 0]))->toBe(['value' => 0])
        ->and($second->get('concurrency.test', ['value' => 0]))->toBe(['value' => 0]);

    $first->put('concurrency.test', ['value' => 1]);

    expect(fn () => $second->put('concurrency.test', ['value' => 2]))
        ->toThrow(ValidationException::class);

    expect(UserDomainState::query()
        ->where('user_id', $this->authenticatedUser->id)
        ->where('namespace', 'concurrency.test')
        ->firstOrFail()
        ->payload)->toBe(['value' => 1]);
});

test('pet creation persists a private owner profile and renders only for its owner', function () {
    $response = $this->post(route('actions.perform'), [
        'action' => 'create-pet',
        'title' => 'Milo',
        'category' => 'Dog',
        'detail' => 'Rescue mix',
        'body' => 'Milo enjoys calm neighborhood walks.',
    ]);

    $profile = PetProfile::query()
        ->where('user_id', $this->authenticatedUser->id)
        ->where('name', 'Milo')
        ->firstOrFail();

    expect($profile->visibility)->toBe('private')
        ->and($profile->profile_key)->toStartWith('created-pet-')
        ->and($profile->profile_data)->toMatchArray([
            'story' => 'Milo enjoys calm neighborhood walks.',
            'status' => 'Milo enjoys calm neighborhood walks.',
        ])
        ->and(AuditLog::query()
            ->where('action', 'pet-profile.created')
            ->where('target_id', (string) $profile->id)
            ->exists())->toBeTrue();

    $response->assertRedirect(route('pets.created', ['item' => $profile->profile_key]));
    $this->get(route('pets.created', ['item' => $profile->profile_key]))
        ->assertOk()
        ->assertSee('Milo');

    auth()->logout();

    $this->get(route('pets.created', ['item' => $profile->profile_key]))
        ->assertRedirect(route('login'));
});

test('pet profile and privacy updates authorize the owner and persist durable data', function () {
    $profile = PetProfile::factory()
        ->for($this->authenticatedUser)
        ->create([
            'profile_key' => 'pet-scout',
            'slug' => 'scout',
            'name' => 'Scout',
            'species' => 'Dog',
            'profile_data' => ['story' => 'Original story'],
        ]);

    app(UpdatePetProfile::class)->handle('scout', [
        'title' => 'Scout Carter',
        'category' => 'Border Collie mix',
        'detail' => 'Ready for quiet walks',
        'body' => 'Scout prefers low-pressure introductions.',
    ]);

    app(UpdatePetProfilePrivacy::class)->handle('scout', [
        'location_visibility' => 'owners',
        'posts_visibility' => 'followers',
        'friends_visibility' => 'friends',
        'care_visibility' => 'owners',
        'activity_visibility' => 'private',
    ]);

    $profile->refresh();

    expect($profile->name)->toBe('Scout Carter')
        ->and($profile->breed)->toBe('Border Collie mix')
        ->and($profile->profile_data)->toMatchArray([
            'story' => 'Scout prefers low-pressure introductions.',
            'status' => 'Ready for quiet walks',
            'privacy' => [
                'location' => 'owners',
                'posts' => 'followers',
                'friends' => 'friends',
                'care' => 'owners',
                'activity' => 'private',
            ],
        ])
        ->and(AuditLog::query()
            ->where('target_type', PetProfile::class)
            ->where('target_id', (string) $profile->id)
            ->whereIn('action', ['pet-profile.updated', 'pet-profile.privacy-updated'])
            ->count())->toBe(2);

    $other = User::factory()->create();
    $this->actingAs($other);

    expect(fn () => app(UpdatePetProfile::class)->handle('scout', [
        'title' => 'Unauthorized',
        'category' => 'Unknown',
        'detail' => '',
        'body' => 'This must not be saved.',
    ]))->toThrow(ValidationException::class);

    expect($profile->fresh()->name)->toBe('Scout Carter');
});
