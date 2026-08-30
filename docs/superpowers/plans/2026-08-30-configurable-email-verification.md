# Configurable Email Verification Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add one safe environment-backed switch for PawCircle email verification, automatically activate new and existing eligible accounts while it is disabled, and verify the deployed migration/seeding/runtime state.

**Architecture:** `EmailVerificationMode` is the single typed reader for the environment-backed config value. The central portal boundary, Laravel-compatible `verified` middleware alias, registration Action, verification Livewire component, bounded activation Action/command, and demo seeder all consume that mode so request behavior and persisted `email_verified_at` state cannot diverge. Existing-account activation is an explicit audited operation after a timestamped SQLite backup; there is no schema migration for this feature.

**Tech Stack:** PHP 8.5, Laravel 13, class-based Livewire 4, Eloquent, SQLite, Pest 4, Pint, Larastan.

## Global Constraints

- Work only on `main`; preserve every unrelated Places change already present in the dirty shared tree.
- Read environment values only in `config/*.php`; application code consumes `config/platform.php` through `EmailVerificationMode`.
- `EMAIL_VERIFICATION_ENABLED=true` is the committed and automated-test default.
- `EMAIL_VERIFICATION_ENABLED=false` automatically verifies new registrations, suppresses verification mail, and activates only existing active pending accounts through the explicit command.
- Guests and inactive users remain denied in both modes. Policies, scoped private access, password confirmation, CSRF, throttling, and non-email verification domains are unchanged.
- No schema migration, destructive database rebuild, raw SQL in first-party application code, or automatic reversal of verification timestamps.
- Tests use the isolated repository runner; database-backed suites run sequentially.
- The deployed SQLite database must be backed up before pending migrations, root seeding, or account activation.

---

### Task 1: Configuration-Aware Portal Enforcement

**Files:**
- Create: `app/Services/EmailVerificationMode.php`
- Create: `app/Http/Middleware/EnsureEmailIsVerified.php`
- Create: `tests/Feature/Auth/ConfigurableEmailVerificationTest.php`
- Modify: `.env.example`
- Modify: `config/platform.php`
- Modify: `phpunit.xml`
- Modify: `app/Http/Middleware/RequirePortalAccess.php`
- Modify: `bootstrap/app.php`

**Interfaces:**
- Produces: `EmailVerificationMode::isEnabled(): bool`; only an exact configured boolean `false` disables verification.
- Produces: application middleware alias `verified => App\Http\Middleware\EnsureEmailIsVerified` with Laravel-compatible middleware parameters.
- Consumes: existing `User::hasVerifiedEmail()`, `EnsureActiveUser`, and `RequirePortalAccess` ordering.

- [ ] **Step 1: Write failing configuration and middleware tests**

Add focused tests that keep enabled mode as the baseline and exercise both the central boundary and an explicit `verified` route:

```php
<?php

declare(strict_types=1);

use App\Http\Middleware\EnsureEmailIsVerified;
use App\Models\User;
use App\Services\EmailVerificationMode;
use Illuminate\Support\Facades\Route;

test('email verification is enabled by default and owns the verified alias', function (): void {
    expect(app(EmailVerificationMode::class)->isEnabled())->toBeTrue()
        ->and(app('router')->getMiddleware()['verified'])->toBe(EnsureEmailIsVerified::class);
});

test('only an exact false boolean disables email verification', function (): void {
    config()->set('platform.email_verification_enabled', 'false');

    expect(app(EmailVerificationMode::class)->isEnabled())->toBeTrue();

    config()->set('platform.email_verification_enabled', false);

    expect(app(EmailVerificationMode::class)->isEnabled())->toBeFalse();
});

test('central portal access blocks an active unverified account only while verification is enabled', function (): void {
    $user = User::factory()->unverified()->create();
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
```

- [ ] **Step 2: Run the focused tests and observe the expected red result**

Run:

```bash
php scripts/run-tests.php --compact tests/Feature/Auth/ConfigurableEmailVerificationTest.php
```

Expected: FAIL because `EmailVerificationMode` and the application middleware alias do not exist and the central boundary is still unconditional.

- [ ] **Step 3: Implement the minimal configuration mode and middleware integration**

Add the environment key and fail-closed service:

```dotenv
EMAIL_VERIFICATION_ENABLED=true
```

```php
// config/platform.php
'email_verification_enabled' => env('EMAIL_VERIFICATION_ENABLED', true),
```

```xml
<env name="EMAIL_VERIFICATION_ENABLED" value="true" force="true"/>
```

```php
<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Contracts\Config\Repository as ConfigRepository;

final readonly class EmailVerificationMode
{
    public function __construct(private ConfigRepository $config) {}

    public function isEnabled(): bool
    {
        return $this->config->get('platform.email_verification_enabled', true) !== false;
    }
}
```

```php
<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Services\EmailVerificationMode;
use Closure;
use Illuminate\Auth\Middleware\EnsureEmailIsVerified as LaravelEnsureEmailIsVerified;

final class EnsureEmailIsVerified extends LaravelEnsureEmailIsVerified
{
    public function __construct(private readonly EmailVerificationMode $mode) {}

    public function handle($request, Closure $next, $redirectToRoute = null)
    {
        if (! $this->mode->isEnabled()) {
            return $next($request);
        }

        return parent::handle($request, $next, $redirectToRoute);
    }
}
```

Register the alias in `bootstrap/app.php` and inject `EmailVerificationMode` into `RequirePortalAccess` so only the unverified-account branch becomes conditional:

```php
$middleware->alias([
    'active' => EnsureActiveUser::class,
    'verified' => EnsureEmailIsVerified::class,
]);
```

```php
public function __construct(
    private UnavailableAccountResponse $unavailableAccount,
    private EmailVerificationMode $emailVerification,
) {}

if (
    $this->emailVerification->isEnabled()
    && ! $user->hasVerifiedEmail()
    && ! $this->isUnverifiedRoute($routeName)
    && $routeName !== self::LIVEWIRE_UPDATE_ROUTE
) {
    return redirect()->route('verification.notice');
}
```

- [ ] **Step 4: Run the focused portal tests and verify green**

Run:

```bash
php scripts/run-tests.php --compact tests/Feature/Auth/ConfigurableEmailVerificationTest.php tests/Feature/Auth/PortalAccessBoundaryTest.php
```

Expected: PASS with enabled-mode regressions and disabled-mode access both covered.

---

### Task 2: Registration And Verification Notice Behavior

**Files:**
- Modify: `app/Actions/RegisterUser.php`
- Modify: `app/Livewire/Auth/Register.php`
- Modify: `app/Livewire/Auth/VerifyEmail.php`
- Modify: `tests/Feature/Auth/AuthenticationTest.php`
- Modify: `tests/Feature/Auth/ConfigurableEmailVerificationTest.php`

**Interfaces:**
- Consumes: `EmailVerificationMode::isEnabled(): bool` from Task 1.
- Produces: `RegisterUser::handle(array{name:string,email:string,password:string}): User` with a persisted timestamp in disabled mode.
- Preserves: enabled-mode `Registered` event and Laravel verification notification.

- [ ] **Step 1: Add failing enabled/disabled registration and verification-page tests**

Extend the existing enabled registration test with `Notification::assertSentTo($user, VerifyEmailNotification::class)`. Add disabled-mode contracts:

```php
test('disabled email verification activates registration without sending mail', function (): void {
    Notification::fake();
    config()->set('platform.email_verification_enabled', false);
    auth()->logout();

    Livewire::test(Register::class)
        ->set('form.name', 'Immediate Member')
        ->set('form.email', 'immediate@example.test')
        ->set('form.password', 'Secure-Paw-2026')
        ->set('form.password_confirmation', 'Secure-Paw-2026')
        ->call('register')
        ->assertHasNoErrors()
        ->assertRedirect(route('home'));

    $user = User::query()->where('email', 'immediate@example.test')->firstOrFail();

    expect($user->email_verified_at)->not->toBeNull();
    Notification::assertNothingSent();
    $this->assertAuthenticatedAs($user);
});

test('disabled email verification never renders or resends the verification notice', function (): void {
    Notification::fake();
    config()->set('platform.email_verification_enabled', false);
    $user = User::factory()->unverified()->create();
    $this->actingAs($user);

    $this->get(route('verification.notice'))->assertRedirect(route('home'));

    Livewire::test(VerifyEmail::class)
        ->call('resend')
        ->assertRedirect(route('home'));

    Notification::assertNothingSent();
});
```

- [ ] **Step 2: Run the focused tests and observe the expected red result**

Run:

```bash
php scripts/run-tests.php --compact tests/Feature/Auth/AuthenticationTest.php tests/Feature/Auth/ConfigurableEmailVerificationTest.php
```

Expected: FAIL because disabled registration still dispatches `Registered`, stores a null timestamp, redirects to the notice, and renders/resends that notice.

- [ ] **Step 3: Implement atomic mode-aware registration and notice redirects**

In `RegisterUser`, determine the mode once, create the account in a short transaction, stamp the account with `saveOrFail()` while disabled, and dispatch `Registered` only after the transaction in enabled mode:

```php
$verificationEnabled = $this->emailVerification->isEnabled();

$user = DB::transaction(function () use ($data, $verificationEnabled): User {
    $user = User::query()->create([
        'actor_key' => 'user-'.Str::lower((string) Str::ulid()),
        'name' => trim($data['name']),
        'email' => mb_strtolower(trim($data['email'])),
        'password' => $data['password'],
        'locale' => $this->defaultLocale(),
        'timezone' => $this->defaultTimezone(),
        'status' => UserStatus::Active,
    ]);

    if (! $verificationEnabled) {
        $user->forceFill(['email_verified_at' => now()])->saveOrFail();
    }

    return $user;
});

if ($verificationEnabled) {
    event(new Registered($user));
}
```

Choose the registration redirect from persisted state:

```php
$this->redirectRoute($user->hasVerifiedEmail() ? 'home' : 'verification.notice');
```

Guard both `VerifyEmail::mount()` and `VerifyEmail::resend()` with `EmailVerificationMode`; disabled mode and already verified accounts redirect to `home` before a notification can be sent.

- [ ] **Step 4: Run the focused authentication tests and verify green**

Run:

```bash
php scripts/run-tests.php --compact tests/Feature/Auth/AuthenticationTest.php tests/Feature/Auth/ConfigurableEmailVerificationTest.php tests/Feature/JoinLandingPageTest.php
```

Expected: PASS with one notification in enabled mode, none in disabled mode, and no verification-page rendering while disabled.

---

### Task 3: Bounded Existing-Account Activation And Seeder Consistency

**Files:**
- Create: `app/Actions/ActivatePendingEmailUsers.php`
- Create: `app/Console/Commands/ActivatePendingEmailUsers.php`
- Modify: `database/seeders/DatabaseSeeder.php`
- Modify: `lang/en/auth.php`
- Modify: `lang/lt/auth.php`
- Modify: `lang/ru/auth.php`
- Modify: `tests/Feature/Auth/ConfigurableEmailVerificationTest.php`

**Interfaces:**
- Consumes: `EmailVerificationMode::isEnabled(): bool`.
- Produces: `ActivatePendingEmailUsers::handle(bool $dryRun = false, int $chunkSize = 200): array{eligible:int,activated:int}`.
- Produces: `auth:activate-pending-email-users --dry-run --chunk=200`.
- Produces audit action: `account.email-verification-bypassed`, target type `App\Models\User`, no email address in metadata.

- [ ] **Step 1: Write failing activation, refusal, audit, idempotency, and seeder tests**

Add tests that create active pending, blocked pending, suspended pending, and already verified users. Assert dry-run writes nothing; enabled execution fails; disabled execution changes only the active pending user, creates one audit row, and a repeat changes zero. Add a disabled-mode root-seed assertion:

```php
test('pending email activation is bounded audited active-only and idempotent', function (): void {
    config()->set('platform.email_verification_enabled', false);
    $active = User::factory()->unverified()->create();
    $blocked = User::factory()->blocked()->unverified()->create();
    $suspended = User::factory()->suspended()->unverified()->create();

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

    $this->seed(DatabaseSeeder::class);
    $this->seed(DatabaseSeeder::class);

    expect(User::query()
        ->where('status', UserStatus::Active)
        ->whereNull('email_verified_at')
        ->count())->toBe(0)
        ->and(User::query()->count())->toBe(10);
});
```

- [ ] **Step 2: Run the focused tests and observe the expected red result**

Run:

```bash
php scripts/run-tests.php --compact tests/Feature/Auth/ConfigurableEmailVerificationTest.php
```

Expected: FAIL because the Action and command do not exist and the demo unverified identity is recreated with a null timestamp.

- [ ] **Step 3: Implement the bounded transactional Action and command**

Implement the Action completely:

```php
<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\UserStatus;
use App\Models\AuditLog;
use App\Models\User;
use App\Services\EmailVerificationMode;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use LogicException;
use RuntimeException;

final readonly class ActivatePendingEmailUsers
{
    private const string AUDIT_ACTION = 'account.email-verification-bypassed';

    private const string AUDIT_ACTOR = 'system-email-verification-mode';

    public function __construct(private EmailVerificationMode $emailVerification) {}

/** @return array{eligible: int, activated: int} */
public function handle(bool $dryRun = false, int $chunkSize = 200): array
{
    $chunkSize = max(1, min(1000, $chunkSize));
    $eligible = $this->eligibleUsers()->count();

    if ($dryRun) {
        return ['eligible' => $eligible, 'activated' => 0];
    }

    if ($this->emailVerification->isEnabled()) {
        throw new LogicException('Email verification must be disabled before pending accounts can be activated.');
    }

    $activated = 0;

    $this->eligibleUsers()
        ->select('id')
        ->chunkById($chunkSize, function (Collection $users) use (&$activated): void {
            DB::transaction(function () use ($users, &$activated): void {
                $candidateIds = $users->modelKeys();
                $lockedIds = User::query()
                    ->select('id')
                    ->whereKey($candidateIds)
                    ->where('status', UserStatus::Active)
                    ->whereNull('email_verified_at')
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->pluck('id')
                    ->map(static fn (mixed $id): int => (int) $id)
                    ->all();

                if ($lockedIds === []) {
                    return;
                }

                $timestamp = now();
                $updated = User::query()
                    ->whereKey($lockedIds)
                    ->where('status', UserStatus::Active)
                    ->whereNull('email_verified_at')
                    ->update([
                        'email_verified_at' => $timestamp,
                        'updated_at' => $timestamp,
                    ]);

                if ($updated !== count($lockedIds)) {
                    throw new RuntimeException('Pending email activation changed an unexpected number of accounts.');
                }

                $metadata = json_encode(
                    ['reason' => 'email-verification-disabled'],
                    JSON_THROW_ON_ERROR,
                );

                AuditLog::query()->insert(array_map(
                    static fn (int $userId): array => [
                        'actor_key' => self::AUDIT_ACTOR,
                        'actor_role' => 'system',
                        'action' => self::AUDIT_ACTION,
                        'target_type' => User::class,
                        'target_id' => (string) $userId,
                        'metadata' => $metadata,
                        'created_at' => $timestamp,
                        'updated_at' => $timestamp,
                    ],
                    $lockedIds,
                ));

                $activated += $updated;
            }, 3);
        });

    return ['eligible' => $eligible, 'activated' => $activated];
}

    /** @return Builder<User> */
    private function eligibleUsers(): Builder
    {
        return User::query()
            ->where('status', UserStatus::Active)
            ->whereNull('email_verified_at');
    }
}
```

Implement the command with bounded input, a safe failure, and localized output:

```php
<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Actions\ActivatePendingEmailUsers as ActivatePendingEmailUsersAction;
use Illuminate\Console\Command;
use LogicException;

final class ActivatePendingEmailUsers extends Command
{
    protected $signature = 'auth:activate-pending-email-users
        {--dry-run : Report eligible accounts without changing data}
        {--chunk=200 : Accounts processed per transaction}';

    protected $description = 'Activate active accounts waiting for email verification when verification is disabled';

    public function handle(ActivatePendingEmailUsersAction $action): int
    {
        $chunkSize = max(1, min(1000, (int) $this->option('chunk')));

        try {
            $result = $action->handle((bool) $this->option('dry-run'), $chunkSize);
        } catch (LogicException) {
            $this->components->error(__('auth.email_verification_activation.enabled_error'));

            return self::FAILURE;
        }

        $key = $this->option('dry-run')
            ? 'auth.email_verification_activation.dry_run'
            : 'auth.email_verification_activation.complete';

        $this->components->info(__($key, $result));

        return self::SUCCESS;
    }
}
```

Add the English leaves to `lang/en/auth.php`:

```php
'email_verification_activation' => [
    'dry_run' => 'Eligible active accounts: :eligible. No accounts were changed.',
    'complete' => 'Eligible active accounts: :eligible. Activated accounts: :activated.',
    'enabled_error' => 'Disable email verification before activating pending accounts.',
],
```

Add the same contract to `lang/lt/auth.php`:

```php
'email_verification_activation' => [
    'dry_run' => 'Tinkamų aktyvių paskyrų: :eligible. Paskyros nepakeistos.',
    'complete' => 'Tinkamų aktyvių paskyrų: :eligible. Aktyvuotų paskyrų: :activated.',
    'enabled_error' => 'Prieš aktyvuodami laukiančias paskyras išjunkite el. pašto patvirtinimą.',
],
```

Add the same contract to `lang/ru/auth.php`:

```php
'email_verification_activation' => [
    'dry_run' => 'Подходящих активных учётных записей: :eligible. Изменений нет.',
    'complete' => 'Подходящих активных учётных записей: :eligible. Активировано: :activated.',
    'enabled_error' => 'Перед активацией ожидающих учётных записей отключите подтверждение электронной почты.',
],
```

Inject `EmailVerificationMode` into `DatabaseSeeder::run()` and make the deterministic `demo-unverified` seed timestamp conditional without resolving the container as a service locator:

```php
public function run(EmailVerificationMode $emailVerification): void
```

```php
'email_verified_at' => $emailVerification->isEnabled()
    ? null
    : now(),
```

- [ ] **Step 4: Run activation, auth, and repeat-seed tests and verify green**

Run:

```bash
php scripts/run-tests.php --compact tests/Feature/Auth/ConfigurableEmailVerificationTest.php tests/Feature/Auth/AuthenticationTest.php tests/Feature/Database/FactoryAndSeederTest.php tests/Feature/Database/CompleteDatabaseSeederTest.php
```

Expected: PASS; enabled test defaults retain one unverified demo account, while the explicit disabled-mode test retains zero active pending accounts.

---

### Task 4: Canonical Documentation And Generated Traceability

**Files:**
- Modify: `docs/product-requirements.md`
- Modify: `docs/system-requirements.md`
- Modify: `docs/security.md`
- Modify: `docs/authorization.md`
- Modify: `docs/testing.md`
- Modify: `docs/seeding.md`
- Modify: `docs/deployment.md`
- Modify: `docs/implementation-plan.md`
- Modify: `scripts/generate-compliance-matrix.php`
- Regenerate: `docs/requirements/compliance-matrix.md`
- Modify: `CHANGELOG.md`

**Interfaces:**
- Consumes: verified Task 1-3 behavior and exact test paths.
- Produces: conditional requirement language and generator-owned traceability evidence.

- [ ] **Step 1: Update canonical requirements and operational documentation**

Make `PRD-IDENTITY-001` and `SYS-AUTH-005` explicit that proof-of-email ownership is required when the configured mode is enabled. Record that disabled mode stamps accounts without claiming independent ownership proof. Add deployment order: configure, clear cached config, back up, migrate, seed only in allowed non-production environments, dry-run activation, execute activation, repeat it, verify counts/integrity, and warm caches.

Update the implementation-plan statuses from `planned` only when the corresponding observed checks support the new status.

- [ ] **Step 2: Update the first-party compliance generator and regenerate output**

Add `EmailVerificationMode`, the custom middleware, activation Action/command, the configurable feature test, and seed coverage to the `PRD-IDENTITY-001`, `SYS-AUTH-005`, `SEC-AUTH-004`, and applicable operations evidence. Extend the auth verification command to include the new test file.

Run:

```bash
php scripts/generate-compliance-matrix.php > /tmp/pawcircle-compliance-matrix.md
diff -u docs/requirements/compliance-matrix.md /tmp/pawcircle-compliance-matrix.md
```

Expected: only reviewed requirement summary/evidence rows differ. Apply the generated output through the repository's first-party generator command and confirm a second generation is byte-identical.

- [ ] **Step 3: Run documentation and architecture checks**

Run:

```bash
php scripts/run-tests.php --compact tests/Feature/ArchitectureComplianceTest.php
git diff --check
```

Expected: PASS with no `env()` use outside config, no generated-output drift, and no whitespace errors.

---

### Task 5: Deployed Environment, Migration, Seeding, Activation, And Final Verification

**Files:**
- Modify outside Git: `.env`
- Create outside Git: `storage/app/private/backups/database-before-email-verification-<UTC timestamp>.sqlite`
- Mutate runtime data: `database/database.sqlite`
- Modify after evidence: `docs/implementation-plan.md`, `docs/testing.md`, `docs/deployment.md`, `CHANGELOG.md`

**Interfaces:**
- Consumes: `auth:activate-pending-email-users`, the deployed SQLite connection, and all pending repository migrations.
- Produces: `EMAIL_VERIFICATION_ENABLED=false`, migrated/seeded runtime, zero active pending users, retained backup, and exact verification evidence.

- [ ] **Step 1: Run pre-mutation code gates**

Run targeted tests, formatting, and static analysis before touching deployed data:

```bash
php scripts/run-tests.php --compact tests/Feature/Auth/ConfigurableEmailVerificationTest.php tests/Feature/Auth/AuthenticationTest.php tests/Feature/Auth/PortalAccessBoundaryTest.php tests/Feature/JoinLandingPageTest.php tests/Feature/Database/FactoryAndSeederTest.php tests/Feature/Database/CompleteDatabaseSeederTest.php
vendor/bin/pint --dirty
PAO_DISABLE=1 vendor/bin/phpstan analyse --memory-limit=1G
```

Expected: all commands exit `0`. If a Places-owned failure appears, preserve it and report the exact unrelated blocker instead of modifying that slice without ownership.

- [ ] **Step 2: Set the deployed environment mode and clear cached configuration**

Edit `.env` through the controlled patch mechanism so it contains exactly:

```dotenv
EMAIL_VERIFICATION_ENABLED=false
```

Then run:

```bash
scripts/artisan-runtime optimize:clear
scripts/artisan-runtime about --only=environment
```

Expected: local deployed environment boots with debug off; a direct config probe reports email verification disabled without printing secrets.

- [ ] **Step 3: Create and verify a timestamped SQLite backup**

Use the SQLite online backup command as the runtime account, store it under the private application tree, and verify it before any migration or seed:

```bash
install -d -m 0770 -o www -g www storage/app/private/backups
BACKUP_STAMP="$(date -u +%Y%m%dT%H%M%SZ)"
runuser -u www -- sqlite3 database/database.sqlite ".backup 'storage/app/private/backups/database-before-email-verification-${BACKUP_STAMP}.sqlite'"
runuser -u www -- sqlite3 "storage/app/private/backups/database-before-email-verification-${BACKUP_STAMP}.sqlite" "PRAGMA integrity_check;"
```

Expected: backup exists, is owned by `www`, has non-zero size, and prints `ok`.

- [ ] **Step 4: Inspect and run pending migrations and the allowed root seeder**

Run:

```bash
scripts/artisan-runtime migrate:status
scripts/artisan-runtime migrate --force
scripts/artisan-runtime migrate:status
scripts/artisan-runtime db:seed --force
scripts/artisan-runtime db:seed --force
```

Expected: all pending migrations, including the separately owned Places migrations already present in the tree, are applied successfully; the local/demo-allowed root seeder completes twice without identity/count drift. Never run `migrate:fresh` against this database.

- [ ] **Step 5: Dry-run, execute, and repeat pending-account activation**

Run:

```bash
scripts/artisan-runtime auth:activate-pending-email-users --dry-run --chunk=100
scripts/artisan-runtime auth:activate-pending-email-users --chunk=100
scripts/artisan-runtime auth:activate-pending-email-users --chunk=100
```

Expected: dry-run reports the exact active pending count; first write activates that count; second write activates zero.

- [ ] **Step 6: Verify deployed data and HTTP behavior**

Run bounded count/audit probes through the application, then SQLite integrity and route/config/view cache smokes:

```bash
scripts/artisan-runtime migrate:status
scripts/artisan-runtime config:cache
scripts/artisan-runtime route:cache
scripts/artisan-runtime view:cache
scripts/artisan-runtime about --only=environment
sqlite3 database/database.sqlite "PRAGMA integrity_check;"
```

Expected: zero active users have null `email_verified_at`, activation audit count matches the write result, every migration is `Ran`, caches compile, and SQLite prints `ok`. Smoke registration and authenticated portal access without exposing credentials or emails in retained output.

- [ ] **Step 7: Run final repository gates**

Run:

```bash
composer validate --strict
composer audit
vendor/bin/pint --test
PAO_DISABLE=1 vendor/bin/phpstan analyse --memory-limit=1G
php scripts/run-tests.php --compact
php scripts/verify-fresh-database.php
php scripts/verify-migration-cycle.php
npm audit --audit-level=high
npm run build
git diff --check
git status --short --branch
```

Expected: applicable gates exit `0`. Record any pre-existing or unrelated failure exactly; never convert an unrun or failing gate into a completion claim.

- [ ] **Step 8: Review, document evidence, and commit only the attributable slice**

Review every attributable hunk, rerun affected checks after evidence updates, and stage only the files listed in Tasks 1-4 through a temporary `GIT_INDEX_FILE`. Inspect the complete staged diff and `git diff --check` before committing. Do not stage `.env`, the SQLite database, backup, runtime cache files, or unrelated Places work.

Suggested commit:

```bash
git commit -m "feat: make email verification configurable"
```

Expected: one coherent implementation/test/documentation commit on `main`; push status is reported separately and no push is implied without explicit publication authority.
