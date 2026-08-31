<?php

declare(strict_types=1);

use App\Enums\OnboardingPetChoice;
use App\Enums\OnboardingStep;
use App\Livewire\Auth\Register;
use App\Models\PetProfile;
use App\Models\PetProfileAccessRequest;
use App\Models\PetProfileManager;
use App\Models\User;
use Livewire\Livewire;

test('a newly registered account owns the complete authenticated identity journey without demo data', function (): void {
    config()->set('platform.email_verification_enabled', false);
    auth()->logout();
    $sessionId = session()->getId();

    Livewire::test(Register::class)
        ->set('form.name', '  Andrej Prus  ')
        ->set('form.email', 'ANDREJ-IDENTITY@EXAMPLE.TEST')
        ->set('form.password', 'Secure-Paw-2026')
        ->set('form.password_confirmation', 'Secure-Paw-2026')
        ->call('register')
        ->assertHasNoErrors()
        ->assertRedirect(route('onboarding.show'));

    $user = User::query()->where('email', 'andrej-identity@example.test')->firstOrFail();
    $actor = $user->socialActor()->firstOrFail();
    $now = now();

    $user->onboarding()->firstOrFail()->forceFill([
        'current_step' => OnboardingStep::Complete,
        'pet_relationship_choice' => OnboardingPetChoice::AddLater,
        'introduction_completed_at' => $now,
        'preferences_completed_at' => $now,
        'pet_relationship_completed_at' => $now,
        'privacy_discovery_completed_at' => $now,
        'completed_at' => $now,
        'lock_version' => OnboardingStep::Complete->position(),
    ])->saveOrFail();

    expect($user->name)->toBe('Andrej Prus')
        ->and(auth()->id())->toBe($user->id)
        ->and(session()->getId())->not->toBe($sessionId)
        ->and($user->socialActor()->count())->toBe(1)
        ->and($actor->settings()->count())->toBe(1)
        ->and($user->onboarding()->count())->toBe(1)
        ->and($user->petProfiles()->count())->toBe(0)
        ->and($user->managedPetProfiles()->count())->toBe(0)
        ->and($user->petProfileAccessRequests()->count())->toBe(0)
        ->and(PetProfile::query()->whereBelongsTo($user)->count())->toBe(0)
        ->and(PetProfileManager::query()->whereBelongsTo($user)->count())->toBe(0)
        ->and(PetProfileAccessRequest::query()->whereBelongsTo($user, 'requester')->count())->toBe(0);

    $portal = $this->get(route('preview.feed'))->assertOk();
    $portalXPath = responseXPath($portal);

    $portal
        ->assertSee('Andrej Prus')
        ->assertDontSee('Mia Carter')
        ->assertDontSee('Scout')
        ->assertDontSee('Nori');

    expect($portalXPath->evaluate('string(//*[@data-header-link="profile"]/@href)'))
        ->toBe(route('members.show', $actor));

    $this->get(route('members.show', $actor))
        ->assertOk()
        ->assertSee('Andrej Prus')
        ->assertDontSee('Mia Carter')
        ->assertDontSee('Scout')
        ->assertDontSee('Nori')
        ->assertDontSee('2.4k');
});

test('fresh account prototype surfaces remain empty and expose no fixed identity actions', function (): void {
    $user = User::factory()->onboarded()->create([
        'name' => 'Alice Example',
        'email' => 'alice-empty-surfaces@example.test',
    ]);
    $this->actingAs($user);

    foreach (['messages.index', 'walks.index', 'connections.index'] as $routeName) {
        $this->get(route($routeName))
            ->assertOk()
            ->assertDontSee('Mia Carter')
            ->assertDontSee('Scout')
            ->assertDontSee('Nori')
            ->assertDontSee('ari-jensen', false)
            ->assertDontSee('compose/message', false)
            ->assertDontSee('compose/walk', false);
    }

    expect(route('messages.index'))->not->toContain('mia-carter')
        ->and(app('router')->has('messages.details'))->toBeFalse()
        ->and(app('router')->has('messages.actions'))->toBeFalse()
        ->and(app('router')->has('neighbors.ari'))->toBeFalse();
});
