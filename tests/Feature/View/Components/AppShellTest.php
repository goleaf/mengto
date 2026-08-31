<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Support\Facades\Auth;
test('authenticated shell renders the current users canonical identity in every locale', function (
    string $locale,
): void {
    $user = User::factory()->onboarded()->create([
        'name' => 'Andrej Prus',
        'email' => "andrej-{$locale}@example.test",
        'locale' => $locale,
    ]);
    $actor = $user->socialActor()->firstOrFail();
    $this->actingAs($user);

    $response = $this->get(route('preview.feed'))->assertOk();
    $xpath = responseXPath($response);

    expect(trim((string) $xpath->evaluate(
        'string(//*[@data-header-link="profile"]/*[contains(@class, "header-profile__name")])',
    )))->toBe('Andrej Prus')
        ->and($xpath->evaluate('string(//*[@data-header-link="profile"]/@href)'))
        ->toBe(route('members.show', $actor))
        ->and($xpath->evaluate('string(//*[@data-header-link="profile"]/@aria-label)'))
        ->toBe(trans('navigation.utility.profile_for', ['name' => 'Andrej Prus'], $locale))
        ->and($xpath->query('//*[@data-header-link="profile"]//img')->length)->toBe(0)
        ->and($xpath->evaluate('string(//*[@data-header-link="profile"]/@href)'))
        ->not->toBe(route('profile.mia'));
})->with(['en', 'lt', 'ru']);

test('authenticated shell replaces all current identity values when the account changes', function (): void {
    $alice = User::factory()->onboarded()->create([
        'name' => 'Alice Example',
        'email' => 'alice@example.test',
    ]);
    $bob = User::factory()->onboarded()->create([
        'name' => 'Bob Example',
        'email' => 'bob@example.test',
    ]);
    $aliceActor = $alice->socialActor()->firstOrFail();
    $bobActor = $bob->socialActor()->firstOrFail();

    $this->actingAs($alice);
    $aliceHeader = responseXPath($this->get(route('preview.feed'))->assertOk());

    expect(trim((string) $aliceHeader->evaluate(
        'string(//*[@data-header-link="profile"]/*[contains(@class, "header-profile__name")])',
    )))->toBe('Alice Example')
        ->and($aliceHeader->evaluate('string(//*[@data-header-link="profile"]/@href)'))
        ->toBe(route('members.show', $aliceActor))
        ->not->toBe(route('members.show', $bobActor));

    Auth::logout();
    $this->actingAs($bob);
    $bobHeader = responseXPath($this->get(route('preview.feed'))->assertOk());

    expect(trim((string) $bobHeader->evaluate(
        'string(//*[@data-header-link="profile"]/*[contains(@class, "header-profile__name")])',
    )))->toBe('Bob Example')
        ->and($bobHeader->evaluate('string(//*[@data-header-link="profile"]/@href)'))
        ->toBe(route('members.show', $bobActor))
        ->not->toBe(route('members.show', $aliceActor));
});

test('authenticated shell reflects a canonical name change without prototype or session state', function (): void {
    $user = User::factory()->onboarded()->create([
        'name' => 'Before Name',
        'email' => 'renamed@example.test',
    ]);
    $this->actingAs($user);

    $user->update(['name' => 'After Name']);

    $xpath = responseXPath($this->get(route('preview.feed'))->assertOk());

    expect(trim((string) $xpath->evaluate(
        'string(//*[@data-header-link="profile"]/*[contains(@class, "header-profile__name")])',
    )))->toBe('After Name');
});

test('logout removes the previous account identity from the next response', function (): void {
    $user = User::factory()->onboarded()->create([
        'name' => 'Alice Example',
        'email' => 'logout-alice@example.test',
    ]);
    $this->actingAs($user);

    $this->post(route('logout'))->assertRedirect(route('login'));

    $this->get(route('login'))
        ->assertOk()
        ->assertDontSee('Alice Example')
        ->assertDontSee('data-header-link="profile"', false);
    $this->assertGuest();
});
