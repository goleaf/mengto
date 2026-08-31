<?php

declare(strict_types=1);

use App\Actions\ActivatePendingEmailUsers;
use App\Enums\UserStatus;
use App\Http\Middleware\EnsureEmailIsVerified;
use App\Livewire\Auth\Register;
use App\Livewire\Auth\VerifyEmail;
use App\Models\AuditLog;
use App\Models\User;
use App\Notifications\VerifyEmailNotification;
use App\Services\EmailVerificationMode;
use App\Services\SocialActorResolver;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Route;
use Livewire\Livewire;

test('email verification is enabled by default and owns the verified alias', function (): void {
    expect(app(EmailVerificationMode::class)->isEnabled())->toBeTrue()
        ->and(app('router')->getMiddleware()['verified'])->toBe(EnsureEmailIsVerified::class);
});

test('only an exact false boolean disables email verification', function (): void {
    $user = User::factory()->unverified()->create();
    app(SocialActorResolver::class)->provisionPrivateForUser($user);
    $this->actingAs($user);

    config()->set('platform.email_verification_enabled', 'false');

    $this->get(route('content.index'))
        ->assertRedirect(route('verification.notice'));

    config()->set('platform.email_verification_enabled', false);

    $this->get(route('content.index'))->assertSuccessful();
});

test('central portal access blocks an active unverified account only while verification is enabled', function (): void {
    $user = User::factory()->unverified()->create();
    app(SocialActorResolver::class)->provisionPrivateForUser($user);
    $this->actingAs($user);

    $this->get(route('content.index'))
        ->assertRedirect(route('verification.notice'));

    config()->set('platform.email_verification_enabled', false);

    $this->get(route('content.index'))->assertSuccessful();
});

test('explicit verified middleware follows the configured mode', function (): void {
    Route::get('/_test/configurable-email-verification', fn () => response('allowed'))
        ->middleware(['auth', 'active', 'verified'])
        ->name('test.configurable-email-verification');
    Route::getRoutes()->refreshNameLookups();

    $user = User::factory()->unverified()->create();
    $this->actingAs($user);

    $this->get(route('test.configurable-email-verification'))
        ->assertRedirect(route('verification.notice'));

    config()->set('platform.email_verification_enabled', false);

    $this->get(route('test.configurable-email-verification'))
        ->assertOk()
        ->assertSee('allowed');
});

test('disabled verification does not weaken guest or inactive account denial', function (): void {
    config()->set('platform.email_verification_enabled', false);
    auth()->logout();

    $this->get(route('content.index'))->assertRedirect(route('login'));

    $this->actingAs(User::factory()->suspended()->unverified()->create());

    $this->get(route('content.index'))->assertRedirect(route('login'));
    $this->assertGuest();
});

test('registration immediately verifies the account without sending mail when disabled', function (): void {
    Notification::fake();
    Event::fake([Registered::class]);
    config()->set('platform.email_verification_enabled', false);
    auth()->logout();

    Livewire::test(Register::class)
        ->set('form.name', '  Lina Petraitė  ')
        ->set('form.email', 'LINA@example.test')
        ->set('form.password', 'Secure-Paw-2026')
        ->set('form.password_confirmation', 'Secure-Paw-2026')
        ->call('register')
        ->assertHasNoErrors()
        ->assertRedirect(route('onboarding.show'));

    $user = User::query()->where('email', 'lina@example.test')->firstOrFail();

    expect($user->hasVerifiedEmail())->toBeTrue();
    Event::assertDispatched(
        Registered::class,
        fn (Registered $event): bool => $event->user->is($user),
    );
    Notification::assertNotSentTo($user, VerifyEmailNotification::class);
    $this->assertAuthenticatedAs($user);
});

test('verification notice and resend action leave immediately without mail when disabled', function (): void {
    Notification::fake();
    config()->set('platform.email_verification_enabled', false);
    $user = User::factory()->unverified()->create();
    $this->actingAs($user);

    Livewire::test(VerifyEmail::class)
        ->assertRedirect(route('home'));

    Livewire::test(VerifyEmail::class)
        ->call('resend')
        ->assertSet('sent', false);

    Notification::assertNotSentTo($user, VerifyEmailNotification::class);
});

test('pending email activation is bounded audited active-only and idempotent', function (): void {
    config()->set('platform.email_verification_enabled', false);
    $active = User::factory()->unverified()->create();
    $blocked = User::factory()->blocked()->unverified()->create();
    $suspended = User::factory()->suspended()->unverified()->create();
    $verified = User::factory()->create();

    $dryRun = app(ActivatePendingEmailUsers::class)->handle(dryRun: true, chunkSize: 1);

    expect($dryRun)->toBe(['eligible' => 1, 'activated' => 0])
        ->and($active->fresh()?->email_verified_at)->toBeNull();

    $first = app(ActivatePendingEmailUsers::class)->handle(chunkSize: 1);
    $second = app(ActivatePendingEmailUsers::class)->handle(chunkSize: 1);

    expect($first)->toBe(['eligible' => 1, 'activated' => 1])
        ->and($second)->toBe(['eligible' => 0, 'activated' => 0])
        ->and($active->fresh()?->email_verified_at)->not->toBeNull()
        ->and($blocked->fresh()?->email_verified_at)->toBeNull()
        ->and($suspended->fresh()?->email_verified_at)->toBeNull()
        ->and($verified->fresh()?->hasVerifiedEmail())->toBeTrue()
        ->and(AuditLog::query()
            ->where('action', 'account.email-verification-bypassed')
            ->where('target_type', User::class)
            ->where('target_id', (string) $active->id)
            ->count())->toBe(1);
});

test('pending email activation refuses writes while verification is enabled', function (): void {
    $pending = User::factory()->unverified()->create();

    $this->artisan('auth:activate-pending-email-users')->assertFailed();

    expect($pending->fresh()?->email_verified_at)->toBeNull();
});

test('disabled mode root seeding creates no active pending email accounts and remains repeatable', function (): void {
    config()->set('platform.email_verification_enabled', false);
    $before = User::query()->count();

    $this->seed(DatabaseSeeder::class);
    $this->seed(DatabaseSeeder::class);

    expect(User::query()
        ->where('status', UserStatus::Active)
        ->whereNull('email_verified_at')
        ->count())->toBe(0)
        ->and(User::query()->count())->toBe($before + 10);
});
