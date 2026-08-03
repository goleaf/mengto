<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Support\Facades\DB;

test('guest home is private and stores the intended destination', function (): void {
    auth()->logout();

    $this->get(route('home'))
        ->assertRedirect(route('login'))
        ->assertSessionHas('url.intended', route('home'));
});

test('guest home performs no application database queries', function (): void {
    auth()->logout();
    DB::flushQueryLog();
    DB::enableQueryLog();

    $this->get(route('home'))->assertRedirect(route('login'));

    expect(DB::getQueryLog())->toBeEmpty();
});

test('verified member home redirects to the canonical content feed', function (): void {
    $this->actingAs(User::factory()->create());

    $this->get(route('home'))
        ->assertRedirect(route('content.index'));
});

test('unverified member home redirects to email verification', function (): void {
    $this->actingAs(User::factory()->unverified()->create());

    $this->get(route('home'))
        ->assertRedirect(route('verification.notice'));
});

test('inactive member home terminates the session through the account availability rule', function (): void {
    $this->actingAs(User::factory()->suspended()->create());

    $this->get(route('home'))
        ->assertRedirect(route('login'))
        ->assertSessionHas('feedback', __('auth.login.account_unavailable'));

    $this->assertGuest();
});

test('guest account entry renders reviewed content for every supported locale', function (
    string $locale,
    string $htmlLocale,
    string $heading,
): void {
    auth()->logout();

    $this->withSession(['locale' => $locale])
        ->get(route('login'))
        ->assertSuccessful()
        ->assertSee('<html lang="'.$htmlLocale.'">', false)
        ->assertSee($heading)
        ->assertDontSee('>auth.', false);
})->with([
    'English' => ['en', 'en', 'Welcome back'],
    'Lithuanian' => ['lt', 'lt', 'Sveiki sugrįžę'],
    'Russian' => ['ru', 'ru', 'С возвращением'],
]);

test('guest can switch the account entry language through a validated session preference', function (): void {
    auth()->logout();

    $this->from(route('login'))
        ->post(route('locale.update'), ['locale' => 'ru'])
        ->assertRedirect(route('login'))
        ->assertSessionHas('locale', 'ru');

    $this->get(route('login'))
        ->assertSuccessful()
        ->assertSee('<html lang="ru">', false)
        ->assertSee('С возвращением');

    $this->post(route('locale.update'), ['locale' => 'unsupported'])
        ->assertSessionHasErrors('locale');
});
