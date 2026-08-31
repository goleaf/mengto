<?php

declare(strict_types=1);

use App\Models\User;

test('profile editor reads and returns to the authenticated users canonical identity', function (): void {
    $user = User::factory()->onboarded()->create([
        'name' => 'Andrej Prus',
        'email' => 'andrej-editor@example.test',
    ]);
    $actor = $user->socialActor()->firstOrFail();
    $this->actingAs($user);

    $this->get(route('compose', ['kind' => 'profile']))
        ->assertOk()
        ->assertSee('value="Andrej Prus"', false)
        ->assertSee(route('members.show', $actor), false)
        ->assertDontSee('Mia Carter')
        ->assertDontSee('mia-carter', false);
});

test('profile update persists only the canonical user name and redirects to that users actor', function (): void {
    $user = User::factory()->onboarded()->create([
        'name' => 'Before Name',
        'email' => 'canonical-profile-update@example.test',
    ]);
    $actor = $user->socialActor()->firstOrFail();
    $this->actingAs($user);

    $this->post(route('actions.perform'), [
        'action' => 'update-profile',
        'title' => '  Andrej Prus  ',
        'body' => 'This prototype-only biography must not become identity state.',
        'location' => 'Prototype location',
        'detail' => 'Prototype status',
    ])->assertRedirect(route('members.show', $actor));

    expect($user->fresh()?->name)->toBe('Andrej Prus');

    $this->get(route('members.show', $actor))
        ->assertOk()
        ->assertSee('Andrej Prus')
        ->assertDontSee('Before Name')
        ->assertDontSee('Mia Carter');
});
