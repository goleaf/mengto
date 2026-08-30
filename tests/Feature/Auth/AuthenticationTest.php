<?php

declare(strict_types=1);

use App\Livewire\Auth\ConfirmPassword;
use App\Livewire\Auth\ForgotPassword;
use App\Livewire\Auth\Login;
use App\Livewire\Auth\Register;
use App\Livewire\Auth\ResetPassword;
use App\Livewire\Auth\VerifyEmail;
use App\Livewire\ProfileSettings;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword as ResetPasswordNotification;
use Illuminate\Auth\Notifications\VerifyEmail as VerifyEmailNotification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\URL;
use Livewire\Livewire;

test('guest can render localized authentication pages', function () {
    auth()->logout();

    $this->withSession(['locale' => 'ru'])
        ->get(route('login'))
        ->assertOk()
        ->assertSee('Вход')
        ->assertSee('Электронная почта')
        ->assertSee('wire:offline', false)
        ->assertSee(__('auth.connection.offline'));
});

test('guest account forms share the responsive accessible authentication shell', function () {
    auth()->logout();

    $pages = [
        route('login') => 'login',
        route('register') => 'register',
        route('password.request') => 'forgot-password',
        route('password.reset', ['token' => 'layout-token']) => 'reset-password',
    ];

    foreach ($pages as $url => $page) {
        $response = $this->get($url)
            ->assertOk()
            ->assertSee('data-auth-shell', false)
            ->assertSee('data-auth-page="'.$page.'"', false)
            ->assertSee('class="auth-story"', false)
            ->assertSee('class="auth-card"', false);

        expect(substr_count((string) $response->getContent(), '<h1'))
            ->toBe(1);
    }
});

test('guest account links use full document navigation without duplicate asset preloads', function () {
    auth()->logout();

    foreach ([route('login'), route('register'), route('password.request')] as $url) {
        $this->get($url)
            ->assertOk()
            ->assertDontSee('wire:navigate', false);
    }
});

test('authenticated account forms share the responsive accessible authentication shell', function () {
    $unverified = User::factory()->unverified()->create();
    $this->actingAs($unverified);

    $pages = [
        route('verification.notice') => 'verify-email',
        route('password.confirm') => 'confirm-password',
    ];

    foreach ($pages as $url => $page) {
        $response = $this->get($url)
            ->assertOk()
            ->assertSee('data-auth-shell', false)
            ->assertSee('data-auth-page="'.$page.'"', false)
            ->assertSee('class="auth-story"', false)
            ->assertSee('class="auth-card"', false);

        expect(substr_count((string) $response->getContent(), '<h1'))
            ->toBe(1);
    }
});

test('authentication fields keep explicit labels and browser autocomplete contracts', function () {
    auth()->logout();

    $this->get(route('login'))
        ->assertOk()
        ->assertSee('for="login-email"', false)
        ->assertSee('id="login-email"', false)
        ->assertSee('autocomplete="email"', false)
        ->assertSee('for="login-password"', false)
        ->assertSee('autocomplete="current-password"', false);

    $this->get(route('register'))
        ->assertOk()
        ->assertSee('autocomplete="name"', false)
        ->assertSee('autocomplete="new-password"', false)
        ->assertSee('register-password-feedback', false);
});

test('every authentication password field has an independent localized visibility toggle', function () {
    auth()->logout();

    $guestPages = [
        route('login') => ['login-password'],
        route('register') => ['register-password', 'register-password-confirmation'],
        route('password.reset', ['token' => 'visibility-token']) => ['reset-password', 'reset-password-confirmation'],
    ];

    foreach ($guestPages as $url => $passwordIds) {
        $content = (string) $this->get($url)->assertOk()->getContent();

        expect(substr_count($content, 'class="auth-field__password-toggle"'))
            ->toBe(count($passwordIds));

        foreach ($passwordIds as $passwordId) {
            expect($content)
                ->toContain('data-password-visibility="'.$passwordId.'"')
                ->toContain('aria-controls="'.$passwordId.'"')
                ->toContain('x-bind:type="passwordVisible ? \'text\' : \'password\'"')
                ->toContain('data-show-label="'.__('auth.password_visibility.show').'"')
                ->toContain('data-hide-label="'.__('auth.password_visibility.hide').'"');
        }
    }

    expect((string) $this->get(route('login'))->getContent())
        ->not->toContain('data-password-visibility="login-email"');

    $this->actingAs(User::factory()->unverified()->create());

    $this->get(route('password.confirm'))
        ->assertOk()
        ->assertSee('data-password-visibility="confirm-password"', false)
        ->assertSee('aria-controls="confirm-password"', false);

    auth()->logout();

    foreach ([
        'en' => ['Show password', 'Hide password'],
        'lt' => ['Rodyti slaptažodį', 'Slėpti slaptažodį'],
        'ru' => ['Показать пароль', 'Скрыть пароль'],
    ] as $locale => [$showLabel, $hideLabel]) {
        $this->withSession(['locale' => $locale])
            ->get(route('register'))
            ->assertOk()
            ->assertSee('data-show-label="'.$showLabel.'"', false)
            ->assertSee('data-hide-label="'.$hideLabel.'"', false);
    }
});

test('stateful authentication forms expose localized unsaved state feedback', function () {
    auth()->logout();

    Livewire::test(Register::class)
        ->assertSee('wire:dirty', false)
        ->assertSee(__('auth.form.unsaved'));

    Livewire::test(ResetPassword::class, ['token' => 'test-token'])
        ->assertSee('wire:dirty', false)
        ->assertSee(__('auth.form.unsaved'));
});

test('every authentication component renders precise loading and offline feedback', function () {
    Livewire::test(Login::class)
        ->assertSee('wire:loading', false)
        ->assertSee('wire:target="authenticate"', false);
    Livewire::test(Register::class)
        ->assertSee('wire:loading', false)
        ->assertSee('wire:target="register"', false);
    Livewire::test(ForgotPassword::class)
        ->assertSee('wire:loading', false)
        ->assertSee('wire:target="sendResetLink"', false);
    Livewire::test(ResetPassword::class, ['token' => 'test-token'])
        ->assertSee('wire:loading', false)
        ->assertSee('wire:target="resetPassword"', false);
    Livewire::test(ConfirmPassword::class)
        ->assertSee('wire:loading', false)
        ->assertSee('wire:target="confirm"', false);

    $unverified = User::factory()->unverified()->create();
    $this->actingAs($unverified);

    Livewire::test(VerifyEmail::class)
        ->assertSee('wire:loading', false)
        ->assertSee('wire:target="resend"', false);
});

test('protected private routes redirect guests to login', function () {
    auth()->logout();

    $this->get(route('medical-records.index'))
        ->assertRedirect(route('login'));
});

test('authenticated user can confirm a password before a high-risk action', function () {
    session()->put('url.intended', route('devices.index'));

    Livewire::test(ConfirmPassword::class)
        ->set('form.password', 'password')
        ->call('confirm')
        ->assertHasNoErrors()
        ->assertRedirect(route('devices.index'));

    expect(session()->has('auth.password_confirmed_at'))->toBeTrue();
});

test('password confirmation rejects parser-confusing intended destinations', function () {
    session()->put('url.intended', '/\\attacker.example/steal');

    Livewire::test(ConfirmPassword::class)
        ->set('form.password', 'password')
        ->call('confirm')
        ->assertHasNoErrors()
        ->assertRedirect(route('home'));

    expect(session()->has('url.intended'))->toBeFalse();
});

test('password confirmation rejects an invalid password without opening the window', function () {
    Livewire::test(ConfirmPassword::class)
        ->set('form.password', 'incorrect-password')
        ->call('confirm')
        ->assertHasErrors(['form.password'])
        ->assertDispatched('auth-validation-failed');

    expect(session()->has('auth.password_confirmed_at'))->toBeFalse();
});

test('password confirmation direct action rejects a guest', function () {
    auth()->logout();

    $this->get(route('password.confirm'))
        ->assertRedirect(route('login'));

    Livewire::test(ConfirmPassword::class)
        ->set('form.password', 'password')
        ->call('confirm')
        ->assertRedirect(route('login'));
});

test('password confirmation rate limits repeated invalid attempts', function () {
    $this->freezeTime();

    foreach (range(1, 5) as $attempt) {
        Livewire::test(ConfirmPassword::class)
            ->set('form.password', 'incorrect-'.$attempt)
            ->call('confirm')
            ->assertHasErrors(['form.password']);
    }

    Livewire::test(ConfirmPassword::class)
        ->set('form.password', 'password')
        ->call('confirm')
        ->assertHasErrors(['form.password'])
        ->assertSee(__('auth.confirm_password.throttled', ['seconds' => 60]));

    expect(session()->has('auth.password_confirmed_at'))->toBeFalse();
});

test('active user can authenticate through livewire', function () {
    auth()->logout();

    Livewire::test(Login::class)
        ->set('form.email', $this->authenticatedUser->email)
        ->set('form.password', 'password')
        ->set('form.remember', true)
        ->call('authenticate')
        ->assertHasNoErrors()
        ->assertRedirect(route('home'));

    $this->assertAuthenticatedAs($this->authenticatedUser);
    expect($this->authenticatedUser->fresh()?->last_login_at)->not->toBeNull();
});

test('login rejects invalid and blocked accounts with the same safe error', function () {
    $blocked = User::factory()->blocked()->create([
        'email' => 'blocked@example.test',
        'password' => 'Correct-Horse-42',
    ]);
    auth()->logout();

    Livewire::test(Login::class)
        ->set('form.email', 'missing@example.test')
        ->set('form.password', 'Wrong-Password-42')
        ->call('authenticate')
        ->assertHasErrors(['form.email'])
        ->assertSee(__('auth.login.failed'))
        ->assertDispatched('auth-validation-failed');

    Livewire::test(Login::class)
        ->set('form.email', $blocked->email)
        ->set('form.password', 'Correct-Horse-42')
        ->call('authenticate')
        ->assertHasErrors(['form.email'])
        ->assertSee(__('auth.login.failed'))
        ->assertDispatched('auth-validation-failed');

    $this->assertGuest();
});

test('registration creates a normalized verified-pending account', function () {
    Notification::fake();
    auth()->logout();
    app()->setLocale('lt');

    Livewire::test(Register::class)
        ->set('form.name', '  Ona Petraitė  ')
        ->set('form.email', 'ONA@example.test')
        ->set('form.password', 'Secure-Paw-2026')
        ->set('form.password_confirmation', 'Secure-Paw-2026')
        ->call('register')
        ->assertHasNoErrors()
        ->assertRedirect(route('verification.notice'));

    $user = User::query()->where('email', 'ona@example.test')->firstOrFail();

    expect($user->name)->toBe('Ona Petraitė')
        ->and($user->actor_key)->toStartWith('user-')
        ->and($user->email_verified_at)->toBeNull()
        ->and($user->locale)->toBe('lt')
        ->and($user->timezone)->toBe('UTC');

    Notification::assertSentTo($user, VerifyEmailNotification::class);
    $this->assertAuthenticatedAs($user);
    app()->setLocale('en');
});

test('registration validates every untrusted account field', function () {
    auth()->logout();

    Livewire::test(Register::class)
        ->set('form.name', 'x')
        ->set('form.email', 'not-an-email')
        ->set('form.password', 'short')
        ->set('form.password_confirmation', 'different')
        ->call('register')
        ->assertHasErrors([
            'form.name',
            'form.email',
            'form.password',
        ]);

    $this->assertGuest();
});

test('registration validates email uniqueness after canonical case normalization', function () {
    User::factory()->create(['email' => 'existing@example.test']);
    auth()->logout();
    $before = User::query()->count();

    Livewire::test(Register::class)
        ->set('form.name', 'Duplicate Member')
        ->set('form.email', 'EXISTING@EXAMPLE.TEST')
        ->set('form.password', 'Secure-Paw-2026')
        ->set('form.password_confirmation', 'Secure-Paw-2026')
        ->call('register')
        ->assertHasErrors(['form.email']);

    expect(User::query()->count())->toBe($before);
    $this->assertGuest();
});

test('stale profile settings cannot mutate preferences after verification is lost', function () {
    config()->set('platform.email_verification_enabled', true);
    $component = Livewire::test(ProfileSettings::class)
        ->set('form.locale', 'ru')
        ->set('form.timezone', 'Europe/Riga');

    $this->authenticatedUser->forceFill(['email_verified_at' => null])->saveOrFail();

    $component
        ->call('save')
        ->assertForbidden();

    expect($this->authenticatedUser->fresh())
        ->locale->toBe('en')
        ->timezone->toBe('Europe/Vilnius');
});

test('registration does not expose language or timezone controls', function () {
    auth()->logout();

    Livewire::test(Register::class)
        ->assertDontSee('register-locale', false)
        ->assertDontSee('register-timezone', false)
        ->assertDontSee('form.locale', false)
        ->assertDontSee('form.timezone', false);
});

test('active user can update language and timezone from profile settings', function () {
    $savedMessage = trans('auth.settings.saved', locale: 'ru');

    Livewire::test(ProfileSettings::class)
        ->assertSet('form.locale', 'en')
        ->assertSet('form.timezone', 'Europe/Vilnius')
        ->assertSee('wire:target="save"', false)
        ->assertSee('wire:offline', false)
        ->set('form.locale', 'ru')
        ->set('form.timezone', 'Europe/Riga')
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect(route('profile.settings'))
        ->assertSet('feedback', $savedMessage);

    expect($this->authenticatedUser->fresh())
        ->locale->toBe('ru')
        ->timezone->toBe('Europe/Riga')
        ->and(session('locale'))->toBe('ru')
        ->and(session('profile-settings-feedback'))->toBe($savedMessage);

    $this->get(route('profile.settings'))
        ->assertSuccessful()
        ->assertSee('lang="ru"', false)
        ->assertSee($savedMessage);
});

test('active user can render the full profile settings route', function () {
    $this->get(route('profile.settings'))
        ->assertSuccessful()
        ->assertSee('data-section="profile-settings"', false)
        ->assertSee('id="profile-settings-locale"', false)
        ->assertSee('id="profile-settings-timezone"', false);
});

test('profile settings reject unsupported account preferences', function () {
    Livewire::test(ProfileSettings::class)
        ->set('form.locale', 'unsupported')
        ->set('form.timezone', 'not-a-timezone')
        ->call('save')
        ->assertHasErrors(['form.locale', 'form.timezone']);

    expect($this->authenticatedUser->fresh())
        ->locale->toBe('en')
        ->timezone->toBe('Europe/Vilnius');
});

test('profile settings route redirects guests to login', function () {
    auth()->logout();

    $this->get(route('profile.settings'))
        ->assertRedirect(route('login'));
});

test('password reset request does not reveal whether an account exists', function () {
    Notification::fake();
    auth()->logout();

    Livewire::test(ForgotPassword::class)
        ->set('email', $this->authenticatedUser->email)
        ->call('sendResetLink')
        ->assertSet('sent', true)
        ->assertHasNoErrors();

    Livewire::test(ForgotPassword::class)
        ->set('email', 'missing@example.test')
        ->call('sendResetLink')
        ->assertSet('sent', true)
        ->assertHasNoErrors();

    Notification::assertSentTo($this->authenticatedUser, ResetPasswordNotification::class);
});

test('password reset request validates malformed input', function () {
    auth()->logout();

    Livewire::test(ForgotPassword::class)
        ->set('email', 'not-an-email')
        ->call('sendResetLink')
        ->assertHasErrors(['email'])
        ->assertSet('sent', false);
});

test('valid password reset token can be consumed once', function () {
    $user = User::factory()->create([
        'email' => 'reset@example.test',
        'password' => 'Before-Password-42',
    ]);
    $token = Password::createToken($user);
    auth()->logout();

    Livewire::test(ResetPassword::class, ['token' => $token])
        ->set('form.email', $user->email)
        ->set('form.password', 'After-Password-84')
        ->set('form.password_confirmation', 'After-Password-84')
        ->call('resetPassword')
        ->assertHasNoErrors()
        ->assertRedirect(route('login'));

    expect(Hash::check('After-Password-84', (string) $user->fresh()?->password))->toBeTrue();

    Livewire::test(ResetPassword::class, ['token' => $token])
        ->set('form.email', $user->email)
        ->set('form.password', 'Another-Password-96')
        ->set('form.password_confirmation', 'Another-Password-96')
        ->call('resetPassword')
        ->assertHasErrors(['form.email']);
});

test('password reset form rejects invalid fields before token consumption', function () {
    auth()->logout();

    Livewire::test(ResetPassword::class, ['token' => 'not-a-valid-token'])
        ->set('form.email', 'not-an-email')
        ->set('form.password', 'short')
        ->set('form.password_confirmation', 'different')
        ->call('resetPassword')
        ->assertHasErrors(['form.email', 'form.password']);
});

test('signed verification link verifies the authenticated account', function () {
    $user = User::factory()->unverified()->create();
    $this->actingAs($user);

    $url = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(30),
        [
            'id' => $user->getKey(),
            'hash' => sha1($user->getEmailForVerification()),
        ],
    );

    $this->get($url)
        ->assertRedirect(route('home'))
        ->assertSessionHas('feedback');

    expect($user->fresh()?->hasVerifiedEmail())->toBeTrue();
});

test('unverified account can resend verification and verified account is redirected', function () {
    Notification::fake();
    $unverified = User::factory()->unverified()->create();
    $this->actingAs($unverified);

    Livewire::test(VerifyEmail::class)
        ->call('resend')
        ->assertSet('sent', true);

    Notification::assertSentTo($unverified, VerifyEmailNotification::class);

    $verified = User::factory()->create();
    $this->actingAs($verified);

    Livewire::test(VerifyEmail::class)
        ->assertRedirect(route('home'));
});

test('blocked authenticated account is logged out before protected access', function () {
    $blocked = User::factory()->blocked()->create();
    $this->actingAs($blocked);

    $this->get(route('messages.index'))
        ->assertRedirect(route('login'))
        ->assertSessionHas('feedback');

    $this->assertGuest();
});

test('logout invalidates authentication and redirects to login', function () {
    $this->post(route('logout'))
        ->assertRedirect(route('login'));

    $this->assertGuest();
});
