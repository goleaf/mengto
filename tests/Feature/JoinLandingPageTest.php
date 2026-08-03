<?php

declare(strict_types=1);

use App\Http\Controllers\HomeController;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

test('guest home is a focused localized join page without member prototype chrome', function (): void {
    auth()->logout();

    $response = $this->get(route('home'));

    $response
        ->assertSuccessful()
        ->assertSee('data-join-page', false)
        ->assertSee('data-join-primary', false)
        ->assertSee('href="'.route('register').'"', false)
        ->assertSee('href="'.route('login').'"', false)
        ->assertSee('rel="canonical"', false)
        ->assertSee('property="og:title"', false)
        ->assertSee('name="description"', false)
        ->assertDontSee('data-site-header', false)
        ->assertDontSee('Mia Carter')
        ->assertDontSee('images.unsplash.com')
        ->assertDontSee('wire:navigate', false);

    $xpath = responseXPath($response);

    expect(Route::getRoutes()->getByName('home')?->getActionName())
        ->toBe(HomeController::class)
        ->and($xpath->query('//main[@id="main-content"]')->length)->toBe(1)
        ->and($xpath->query('//main//h1')->length)->toBe(1)
        ->and($xpath->query('//*[@data-join-primary and @href]')->length)->toBeGreaterThanOrEqual(2)
        ->and($xpath->query('//main//*[@data-join-section]')->length)->toBeGreaterThanOrEqual(5)
        ->and($xpath->query('//header//a[@href="'.route('login').'"]')->length)->toBe(1);
});

test('guest home performs no application database queries', function (): void {
    auth()->logout();
    DB::flushQueryLog();
    DB::enableQueryLog();

    $this->get(route('home'))->assertSuccessful();

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

test('guest home renders reviewed content for every supported locale', function (
    string $locale,
    string $htmlLocale,
    string $headline,
    string $primaryAction,
): void {
    auth()->logout();

    $this->withSession(['locale' => $locale])
        ->get(route('home'))
        ->assertSuccessful()
        ->assertSee('<html lang="'.$htmlLocale.'">', false)
        ->assertSee($headline)
        ->assertSee($primaryAction)
        ->assertDontSee('>join.', false);
})->with([
    'English' => ['en', 'en', 'Your pet’s place in the neighborhood.', 'Create your free profile'],
    'Lithuanian' => ['lt', 'lt', 'Jūsų augintinio vieta kaimynystės bendruomenėje.', 'Sukurkite nemokamą profilį'],
    'Russian' => ['ru', 'ru', 'Место вашего питомца в своём сообществе.', 'Создать бесплатный профиль'],
]);

test('guest can switch the join page language through a validated session preference', function (): void {
    auth()->logout();

    $this->from(route('home'))
        ->post(route('locale.update'), ['locale' => 'ru'])
        ->assertRedirect(route('home'))
        ->assertSessionHas('locale', 'ru');

    $this->get(route('home'))
        ->assertSuccessful()
        ->assertSee('<html lang="ru">', false)
        ->assertSee('Место вашего питомца в своём сообществе.');

    $this->post(route('locale.update'), ['locale' => 'unsupported'])
        ->assertSessionHasErrors('locale');
});
