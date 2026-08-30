<?php

declare(strict_types=1);

use App\Actions\RegisterUser as RegisterUserAction;
use App\Enums\OnboardingStep;
use App\Enums\UserStatus;
use App\Livewire\Auth\ConfirmPassword;
use App\Livewire\Auth\Login;
use App\Livewire\Auth\Register;
use App\Livewire\Auth\VerifyEmail;
use App\Models\User;
use App\Notifications\VerifyEmailNotification;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\Notifications\Events\NotificationSending;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Exceptions;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Livewire\Livewire;

test('registration recovers after a post commit verification delivery failure', function (): void {
    Exceptions::fake();
    Event::listen(NotificationSending::class, static function (): never {
        throw new RuntimeException('Verification transport unavailable.');
    });
    auth()->logout();
    $originalSessionId = session()->getId();

    Livewire::test(Register::class)
        ->set('form.name', 'Recoverable Member')
        ->set('form.email', 'recoverable@example.test')
        ->set('form.password', 'Secure-Paw-2026')
        ->set('form.password_confirmation', 'Secure-Paw-2026')
        ->call('register')
        ->assertHasNoErrors()
        ->assertRedirect(route('verification.notice'));

    $user = User::query()->where('email', 'recoverable@example.test')->firstOrFail();

    $this->assertAuthenticatedAs($user);
    expect($user->onboarding()->firstOrFail()->current_step)
        ->toBe(OnboardingStep::Introduction)
        ->and(session()->getId())->not->toBe($originalSessionId)
        ->and(session('verification_delivery_failed'))->toBeTrue();

    $this->get(route('verification.notice'))
        ->assertSuccessful()
        ->assertSee(__('auth.verification.delivery_failed'));

    Exceptions::assertReported(RuntimeException::class);
});

test('registration treats a deliberately skipped verification notification as undelivered', function (): void {
    Event::listen(NotificationSending::class, static fn (): false => false);

    $result = app(RegisterUserAction::class)->handle([
        'name' => 'Skipped Delivery',
        'email' => 'skipped-delivery@example.test',
        'password' => 'Secure-Paw-2026',
    ]);

    expect($result->verificationNotificationDelivered)->toBeFalse()
        ->and($result->user->hasVerifiedEmail())->toBeFalse();
});

test('registration does not misclassify an unrelated registered listener failure as mail delivery', function (): void {
    Event::listen(Registered::class, static function (): never {
        throw new RuntimeException('Unrelated registered listener failed.');
    });

    expect(fn () => app(RegisterUserAction::class)->handle([
        'name' => 'Listener Failure',
        'email' => 'listener-failure@example.test',
        'password' => 'Secure-Paw-2026',
    ]))->toThrow(RuntimeException::class, 'Unrelated registered listener failed.');
});

test('verification notifications use the recipient locale', function (string $locale, string $globalLocale): void {
    $user = User::factory()->create(['locale' => $locale]);
    app()->setLocale($globalLocale);
    Notification::fake();

    $user->sendEmailVerificationNotification();

    Notification::assertSentTo(
        $user,
        VerifyEmailNotification::class,
        fn (VerifyEmailNotification $notification): bool => $notification->locale === $locale,
    );

    app()->setLocale($locale);
    $notification = (new VerifyEmailNotification)->locale($locale);
    $message = $notification->toMail($user);

    expect($notification->locale)->toBe($locale)
        ->and($message->subject)->toBe(__('auth.verification.mail.subject'))
        ->and($message->introLines)->toContain(__('auth.verification.mail.introduction'))
        ->and($message->actionText)->toBe(__('auth.verification.mail.action'))
        ->and(URL::hasValidSignature(Request::create($message->actionUrl)))->toBeTrue()
        ->and($message->outroLines)->toContain(__('auth.verification.mail.ignore'));
})->with([
    'en recipient under ru runtime' => ['en', 'ru'],
    'lt recipient under en runtime' => ['lt', 'en'],
    'ru recipient under lt runtime' => ['ru', 'lt'],
]);

test('verification resend reports delivery failure without returning a server error', function (): void {
    Exceptions::fake();
    Event::listen(NotificationSending::class, static function (): never {
        throw new RuntimeException('Verification resend transport unavailable.');
    });
    $user = User::factory()->unverified()->create();
    $this->actingAs($user);

    Livewire::test(VerifyEmail::class)
        ->call('resend')
        ->assertSet('sent', false)
        ->assertHasErrors(['resend'])
        ->assertSee(__('auth.verification.delivery_failed'));

    Exceptions::assertReported(RuntimeException::class);
});

test('verification resend treats a deliberately skipped notification as undelivered', function (): void {
    Event::listen(NotificationSending::class, static fn (): false => false);
    $user = User::factory()->unverified()->create();
    $this->actingAs($user);

    Livewire::test(VerifyEmail::class)
        ->call('resend')
        ->assertSet('sent', false)
        ->assertHasErrors(['resend']);
});

test('inactive accounts cannot invoke verification resend directly', function (UserStatus $status): void {
    Notification::fake();
    $user = User::factory()->unverified()->create(['status' => $status]);

    Livewire::actingAs($user)
        ->test(VerifyEmail::class)
        ->assertForbidden();

    Notification::assertNothingSent();
})->with([
    'blocked' => [UserStatus::Blocked],
    'suspended' => [UserStatus::Suspended],
]);

test('a stale verification component cannot resend after account blocking', function (): void {
    Notification::fake();
    $user = User::factory()->unverified()->create();
    $component = Livewire::actingAs($user)->test(VerifyEmail::class)->assertOk();
    User::query()->whereKey($user->id)->update(['status' => UserStatus::Blocked]);

    $component
        ->call('resend')
        ->assertForbidden();

    Notification::assertNothingSent();
});

test('verification resend replaces stale success with the current failure', function (): void {
    Exceptions::fake();
    $failDelivery = false;
    Event::listen(NotificationSending::class, static function () use (&$failDelivery): void {
        if ($failDelivery) {
            throw new RuntimeException('Verification transport unavailable after success.');
        }
    });
    $user = User::factory()->unverified()->create();
    $this->actingAs($user);
    $component = Livewire::test(VerifyEmail::class)
        ->call('resend')
        ->assertSet('sent', true)
        ->assertHasNoErrors('resend');

    $failDelivery = true;

    $component
        ->call('resend')
        ->assertSet('sent', false)
        ->assertHasErrors(['resend']);
});

test('verification resend replaces a stale failure with the current success', function (): void {
    Exceptions::fake();
    $failDelivery = true;
    Event::listen(NotificationSending::class, static function () use (&$failDelivery): void {
        if ($failDelivery) {
            throw new RuntimeException('Verification transport unavailable before recovery.');
        }
    });
    $user = User::factory()->unverified()->create();
    $this->actingAs($user);
    $component = Livewire::test(VerifyEmail::class)
        ->call('resend')
        ->assertSet('sent', false)
        ->assertHasErrors(['resend']);

    $failDelivery = false;

    $component
        ->call('resend')
        ->assertSet('sent', true)
        ->assertHasNoErrors('resend');
});

test('password confirmation cannot consume an intended route before onboarding completes', function (): void {
    $user = User::factory()->onboardingAtPets()->create([
        'password' => 'Secure-Paw-2026',
    ]);
    $this->actingAs($user);
    session()->put('url.intended', route('devices.index'));

    Livewire::test(ConfirmPassword::class)
        ->set('form.password', 'Secure-Paw-2026')
        ->call('confirm')
        ->assertHasNoErrors()
        ->assertRedirect(route('onboarding.show'));

    expect(session('url.intended'))->toBe(route('devices.index'))
        ->and($user->onboarding()->firstOrFail()->current_step)
        ->toBe(OnboardingStep::PetRelationship);
});

test('completed users consume a safe intended destination from every account entry point', function (): void {
    $user = User::factory()->create(['password' => 'Secure-Paw-2026']);
    auth()->logout();
    session()->put('url.intended', route('devices.index'));

    Livewire::test(Login::class)
        ->set('form.email', $user->email)
        ->set('form.password', 'Secure-Paw-2026')
        ->call('authenticate')
        ->assertRedirect(route('devices.index'));

    expect(session()->has('url.intended'))->toBeFalse();

    session()->put('url.intended', route('profile.settings'));

    Livewire::test(VerifyEmail::class)
        ->assertRedirect(route('profile.settings'));

    expect(session()->has('url.intended'))->toBeFalse();
});

test('account entry rejects lifecycle routes as final intended destinations', function (string $unsafeDestination): void {
    $user = User::factory()->create(['password' => 'Secure-Paw-2026']);
    auth()->logout();
    session()->put('url.intended', $unsafeDestination);

    Livewire::test(Login::class)
        ->set('form.email', $user->email)
        ->set('form.password', 'Secure-Paw-2026')
        ->call('authenticate')
        ->assertRedirect(route('home'));

    expect(session()->has('url.intended'))->toBeFalse();
})->with([
    'login' => fn (): string => route('login'),
    'registration' => fn (): string => route('register'),
    'verification notice' => fn (): string => route('verification.notice'),
    'onboarding' => fn (): string => route('onboarding.show'),
    'livewire transport' => fn (): string => route('default-livewire.update'),
    'external host' => ['https://evil.example/steal'],
    'protocol relative' => ['//evil.example/steal'],
    'javascript scheme' => ['javascript:alert(1)'],
    'encoded backslash' => ['/%5C%5Cevil.example/steal'],
    'userinfo' => ['https://evil@petsocial.miniserver.fun/devices'],
    'scheme mismatch' => ['http://petsocial.miniserver.fun/devices'],
    'port mismatch' => ['https://petsocial.miniserver.fun:444/devices'],
    'encoded control byte' => ['/devices%00'],
    'unknown local route' => ['/missing-intended-page'],
]);

test('account entry accepts existing internal product destinations', function (string $destination): void {
    $user = User::factory()->create(['password' => 'Secure-Paw-2026']);
    auth()->logout();
    session()->put('url.intended', $destination);

    Livewire::test(Login::class)
        ->set('form.email', $user->email)
        ->set('form.password', 'Secure-Paw-2026')
        ->call('authenticate')
        ->assertRedirect($destination);

    expect(session()->has('url.intended'))->toBeFalse();
})->with([
    'root relative' => ['/devices?tab=alerts'],
    'exact origin' => ['https://petsocial.miniserver.fun/profile/settings?panel=privacy'],
]);

test('guest verification links do not replace the canonical product destination', function (): void {
    $user = User::factory()->unverified()->create();
    auth()->logout();
    session()->put('url.intended', route('devices.index'));
    $verificationUrl = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(30),
        [
            'id' => $user->id,
            'hash' => sha1($user->getEmailForVerification()),
        ],
    );

    $this->get($verificationUrl)
        ->assertRedirect(route('login'));

    expect(session('url.intended'))->toBe(route('devices.index'));
});

test('verification success feedback remains visible after handoff to onboarding', function (): void {
    $user = User::factory()->unverified()->onboardingIncomplete()->create();
    $this->actingAs($user);
    $verificationUrl = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(30),
        [
            'id' => $user->id,
            'hash' => sha1($user->getEmailForVerification()),
        ],
    );

    $this->get($verificationUrl)
        ->assertRedirect(route('onboarding.show'));

    $this->get(route('onboarding.show'))
        ->assertSuccessful()
        ->assertSee(__('auth.verification.success'));
});

test('completed onboarding remains independent from email verification', function (): void {
    $user = User::factory()->unverified()->onboarded()->create();
    $this->actingAs($user);
    session()->put('url.intended', route('devices.index'));
    $verificationUrl = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(30),
        [
            'id' => $user->id,
            'hash' => sha1($user->getEmailForVerification()),
        ],
    );

    $this->get($verificationUrl)
        ->assertRedirect(route('devices.index'));

    expect($user->fresh()?->hasVerifiedEmail())->toBeTrue()
        ->and($user->fresh()?->hasCompletedOnboarding())->toBeTrue()
        ->and(session()->has('url.intended'))->toBeFalse();
});

test('invalid verification links cannot mutate account state or intended destination', function (string $variant): void {
    $user = User::factory()->unverified()->onboardingIncomplete()->create();
    $this->actingAs($user);
    session()->put('url.intended', route('devices.index'));
    $expiresAt = $variant === 'expired' ? now()->subMinute() : now()->addMinutes(30);
    $verificationUrl = URL::temporarySignedRoute(
        'verification.verify',
        $expiresAt,
        [
            'id' => $user->id,
            'hash' => sha1($user->getEmailForVerification()),
        ],
    );

    if ($variant === 'tampered') {
        $verificationUrl = str_replace(
            sha1($user->getEmailForVerification()),
            str_repeat('a', 40),
            $verificationUrl,
        );
    }

    $this->get($verificationUrl)->assertForbidden();

    expect($user->fresh()?->hasVerifiedEmail())->toBeFalse()
        ->and(session('url.intended'))->toBe(route('devices.index'));
})->with(['tampered', 'expired']);

test('a signed verification link is bound to the authenticated user and is replay safe', function (): void {
    Event::fake([Verified::class]);
    $target = User::factory()->unverified()->onboardingIncomplete()->create();
    $other = User::factory()->unverified()->onboardingIncomplete()->create();
    $verificationUrl = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(30),
        [
            'id' => $target->id,
            'hash' => sha1($target->getEmailForVerification()),
        ],
    );

    $this->actingAs($other)
        ->get($verificationUrl)
        ->assertForbidden();

    expect($target->fresh()?->hasVerifiedEmail())->toBeFalse()
        ->and($other->fresh()?->hasVerifiedEmail())->toBeFalse();

    $this->actingAs($target)
        ->get($verificationUrl)
        ->assertRedirect(route('onboarding.show'));
    $this->get($verificationUrl)
        ->assertRedirect(route('onboarding.show'));

    Event::assertDispatchedTimes(Verified::class, 1);
});

test('the first guest product destination wins until account entry completes', function (): void {
    auth()->logout();

    $this->get(route('devices.index'))
        ->assertRedirect(route('login'));
    $this->get(route('profile.settings'))
        ->assertRedirect(route('login'));

    expect(session('url.intended'))->toBe(route('devices.index'));
});

test('logout and login resume the persisted onboarding step', function (): void {
    $user = User::factory()->onboardingAtPrivacy()->create([
        'password' => 'Secure-Paw-2026',
        'locale' => 'ru',
    ]);
    $this->actingAs($user);

    $this->post(route('logout'))
        ->assertRedirect(route('login'));
    $guestSessionId = session()->getId();

    Livewire::test(Login::class)
        ->set('form.email', $user->email)
        ->set('form.password', 'Secure-Paw-2026')
        ->call('authenticate')
        ->assertRedirect(route('onboarding.show'));

    expect($user->onboarding()->firstOrFail()->current_step)
        ->toBe(OnboardingStep::PrivacyDiscovery)
        ->and(session()->getId())->not->toBe($guestSessionId)
        ->and(session('locale'))->toBe('ru');
});
