# Authentication Password Visibility Toggle Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add one localized, accessible show/hide button to every shared authentication password field.

**Architecture:** Keep visibility as independent Alpine state inside the existing passive `auth-field` Blade component. Render a masked input as the no-JavaScript baseline, bind only its browser type, and retain all Livewire form state and server authentication behavior unchanged. Extend the deliberate auth SCSS layer and existing Laravel translation catalogue rather than adding a package or page-specific implementation.

**Tech Stack:** Laravel 13, Blade, Livewire 4 bundled Alpine, Lucide Blade icons, SCSS, Pest 4.

## Global Constraints

- Work only on `main` and preserve every unrelated staged, unstaged, and untracked change.
- Do not add Volt, a second Alpine installation, a JavaScript package, or dynamic Tailwind classes.
- Keep Blade passive and keep password values out of browser persistence, logs, and new server requests.
- Preserve `en`, `lt`, and `ru` with matching stable keys.
- Keep each password masked by default and each toggle independent.
- Retain visible focus, forced-colors usability, and a minimum 44-pixel target.

---

### Task 1: Protect The Shared Password-Field Contract

**Files:**
- Modify: `tests/Feature/Auth/AuthenticationTest.php`

**Interfaces:**
- Consumes: auth routes and the existing `x-auth-field` component.
- Produces: a rendering contract keyed by `data-password-visibility`, `aria-controls`, `x-bind:type`, and localized labels.

- [ ] **Step 1: Write the failing rendering test**

Add a Pest test that renders login, registration, reset, and confirmation
pages, then checks all expected password IDs and excludes ordinary inputs:

```php
test('every authentication password field has an independent localized visibility toggle', function () {
    auth()->logout();

    $guestPages = [
        route('login') => ['login-password'],
        route('register') => ['register-password', 'register-password-confirmation'],
        route('password.reset', ['token' => 'visibility-token']) => ['reset-password', 'reset-password-confirmation'],
    ];

    foreach ($guestPages as $url => $passwordIds) {
        $content = (string) $this->get($url)->assertOk()->getContent();

        foreach ($passwordIds as $passwordId) {
            expect($content)
                ->toContain('data-password-visibility="'.$passwordId.'"')
                ->toContain('aria-controls="'.$passwordId.'"');
        }
    }

    expect((string) $this->get(route('login'))->getContent())
        ->not->toContain('data-password-visibility="login-email"');

    $this->actingAs(User::factory()->unverified()->create());

    $this->get(route('password.confirm'))
        ->assertOk()
        ->assertSee('data-password-visibility="confirm-password"', false)
        ->assertSee('aria-controls="confirm-password"', false);
});
```

- [ ] **Step 2: Run the focused test and observe RED**

Run:

```bash
php artisan test --compact tests/Feature/Auth/AuthenticationTest.php --filter='visibility toggle'
```

Expected: failure because `data-password-visibility` and the toggle button do
not exist yet.

### Task 2: Implement The Shared Toggle

**Files:**
- Modify: `resources/views/components/auth-field.blade.php`
- Modify: `resources/scss/_auth.scss`
- Modify: `lang/en/auth.php`
- Modify: `lang/lt/auth.php`
- Modify: `lang/ru/auth.php`

**Interfaces:**
- Consumes: `type="password"`, the field `id`, Livewire's bundled Alpine, and `x-ui-icon`.
- Produces: one local `passwordVisible` boolean and one labelled native button per password input.

- [ ] **Step 1: Add matching locale keys**

Add the following `password_visibility` group to each auth catalogue:

```php
// lang/en/auth.php
'password_visibility' => [
    'show' => 'Show password',
    'hide' => 'Hide password',
],

// lang/lt/auth.php
'password_visibility' => [
    'show' => 'Rodyti slaptažodį',
    'hide' => 'Slėpti slaptažodį',
],

// lang/ru/auth.php
'password_visibility' => [
    'show' => 'Показать пароль',
    'hide' => 'Скрыть пароль',
],
```

- [ ] **Step 2: Enhance only password fields in the shared component**

Wrap the input in `auth-field__control`; for password fields initialize
`x-data="{ passwordVisible: false }"`, bind the input type, and render a native
button with static and Alpine-updated accessible state:

```blade
<div
    @class([
        'auth-field__control',
        'auth-field__control--password' => $type === 'password',
    ])
    @if ($type === 'password')
        x-data="{ passwordVisible: false }"
        data-password-visibility="{{ $id }}"
    @endif
>
    <input
        type="{{ $type }}"
        @if ($type === 'password') x-bind:type="passwordVisible ? 'text' : 'password'" @endif
    >

    @if ($type === 'password')
        <button
            type="button"
            class="auth-field__password-toggle"
            aria-controls="{{ $id }}"
            aria-label="{{ __('auth.password_visibility.show') }}"
            aria-pressed="false"
            data-show-label="{{ __('auth.password_visibility.show') }}"
            data-hide-label="{{ __('auth.password_visibility.hide') }}"
            x-on:click="passwordVisible = ! passwordVisible"
            x-bind:aria-label="passwordVisible ? $el.dataset.hideLabel : $el.dataset.showLabel"
            x-bind:aria-pressed="passwordVisible"
        >
            <span class="auth-field__password-icon" x-show="! passwordVisible">
                <x-ui-icon name="eye" size="sm" />
            </span>
            <span class="auth-field__password-icon" x-cloak x-show="passwordVisible">
                <x-ui-icon name="eye-off" size="sm" />
            </span>
        </button>
    @endif
</div>
```

Retain all current input IDs, names, autocomplete values, Livewire attributes,
help/error associations, and the component attribute bag.

- [ ] **Step 3: Add contained auth styles**

Add semantic styles under `.auth-field`:

```scss
&__control {
    position: relative;
    min-inline-size: 0;
}

&__control--password &__input {
    padding-inline-end: 3.5rem;
}

&__password-toggle {
    position: absolute;
    inset-block: 0;
    inset-inline-end: 0.25rem;
    display: grid;
    inline-size: $touch-target;
    block-size: $touch-target;
    margin-block: auto;
    place-items: center;
    border: 0;
    border-radius: $control-radius;
    background: transparent;
    color: $muted;
    cursor: pointer;

    @include control-transition;
    @include focus-ring;
}

&__password-toggle:hover,
&__password-toggle[aria-pressed='true'] {
    color: $leaf;
}

&__password-icon {
    display: grid;
    place-items: center;
}

&__password-icon[x-cloak] {
    display: none;
}
```

Include the button in the existing forced-colors boundary.

- [ ] **Step 4: Run GREEN verification**

Run:

```bash
php artisan test --compact tests/Feature/Auth/AuthenticationTest.php --filter='visibility toggle'
php artisan test --compact tests/Feature/Auth/AuthenticationTest.php
```

Expected: both commands pass with zero failures.

### Task 3: Document And Verify The Runtime Boundary

**Files:**
- Modify: `docs/frontend.md`
- Modify: `docs/accessibility.md`
- Verify: all files from Tasks 1 and 2

**Interfaces:**
- Consumes: the verified shared-component behavior.
- Produces: current frontend/accessibility documentation and release evidence.

- [ ] **Step 1: Update canonical auth documentation**

Record that the shared password field uses a localized native toggle, remains
masked without JavaScript, keeps visibility state local to each field, and
retains a 44-pixel accessible target.

- [ ] **Step 2: Run affected quality gates**

Run:

```bash
php artisan test --compact tests/Feature/Auth/AuthenticationTest.php tests/Feature/LocalizationTest.php tests/Feature/ResponsiveInterfaceTest.php tests/Feature/IconSystemContractTest.php tests/Feature/ArchitectureComplianceTest.php
vendor/bin/pint --test
PAO_DISABLE=1 vendor/bin/phpstan analyse --memory-limit=1G --no-progress
php artisan view:cache
npm run build
```

Expected: every command exits `0`. Clear compiled views after the smoke only if
the existing deployment workflow requires it.

- [ ] **Step 3: Inspect the complete attributable diff**

Run:

```bash
git diff --check
git diff -- resources/views/components/auth-field.blade.php resources/scss/_auth.scss lang/en/auth.php lang/lt/auth.php lang/ru/auth.php tests/Feature/Auth/AuthenticationTest.php docs/frontend.md docs/accessibility.md docs/superpowers/specs/2026-08-30-auth-password-visibility-toggle.md docs/superpowers/plans/2026-08-30-auth-password-visibility-toggle.md
```

Expected: no whitespace errors, no password values or secrets, no page-specific
duplication, and no unrelated changes in the attributable diff.
