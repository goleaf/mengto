<?php

declare(strict_types=1);

use App\Models\PetProfile;
use App\Models\User;
use App\Services\CreatedContentPresenter;
use App\Services\FeedPresenter;
use App\Services\PrototypeState;

test('authenticated composers comments and posts use the current user actor identity', function (): void {
    $user = User::factory()->onboarded()->create([
        'name' => 'Andrej Prus',
        'email' => 'andrej-content@example.test',
    ]);
    $actor = $user->socialActor()->firstOrFail();
    $this->actingAs($user);

    $this->get(route('compose', ['kind' => 'post']))
        ->assertOk()
        ->assertSee('value="'.$actor->actor_key.'"', false)
        ->assertSee('Andrej Prus')
        ->assertDontSee('Mia Carter');

    $this->post(route('actions.perform'), [
        'action' => 'create-post',
        'identity' => $actor->actor_key,
        'format' => 'text',
        'body' => 'A canonical current-user publication.',
        'topic' => 'community',
        'media' => 'none',
        'audience' => 'public',
        'comment_policy' => 'all',
        'sensitive' => 'no',
        'intent' => 'published',
    ])->assertRedirect(route('home', ['feed' => 'home']));

    $record = app(PrototypeState::class)->posts()[0];
    $post = app(FeedPresenter::class)->post($record['key']);

    $this->post(route('actions.perform'), [
        'action' => 'create-comment',
        'target' => $record['key'],
        'body' => 'A comment from the authenticated account.',
    ])->assertRedirect(route('posts.show', ['post' => $record['key']]));

    $comment = app(PrototypeState::class)->comments($record['key'])[0];

    expect($record['identity'])->toBe($actor->actor_key)
        ->and($post)->not->toBeNull()
        ->and($post['author'])->toBe('Andrej Prus')
        ->and($post['author_route'])->toBe('members.show')
        ->and($post['author_parameters'])->toBe(['socialActor' => $actor->actor_key])
        ->and($post['avatar'])->toBeNull()
        ->and($comment['author'])->toBe('Andrej Prus')
        ->and($comment['pet'])->toBe('')
        ->and($comment['initials'])->toBe('AP');
});

test('current user pet presentation cannot include another accounts visible pet', function (): void {
    $user = User::factory()->onboarded()->create(['name' => 'Alice Example']);
    $other = User::factory()->onboarded()->create(['name' => 'Bob Example']);
    $this->actingAs($user);

    PetProfile::factory()->for($user)->create([
        'profile_key' => 'created-pet-alice',
        'name' => 'Alice Pet',
    ]);
    PetProfile::factory()->for($other)->create([
        'profile_key' => 'created-pet-bob',
        'name' => 'Bob Pet',
    ]);

    expect(collect(app(CreatedContentPresenter::class)->pets())->pluck('name')->all())
        ->toBe(['Alice Pet']);
});
