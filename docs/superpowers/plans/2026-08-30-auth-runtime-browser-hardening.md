# Authentication Runtime And Browser Hardening Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Prevent mixed-owner Laravel runtime files and verify authentication behavior in Chromium.

**Architecture:** Route production Artisan invocations through one shell boundary that delegates root invocations to the PHP-FPM account and refuses unsafe unprivileged execution. Keep browser verification connected and operator-owned, while retaining Laravel feature tests as the deterministic regression layer.

Keep PHPUnit isolated from deployed config and storage with dedicated
`APP_CONFIG_CACHE` and `LARAVEL_STORAGE_PATH` boundaries plus executable
regression assertions for the `testing` environment and SQLite `:memory:`
database.

**Tech Stack:** Bash, PHP 8.5, Laravel 13, Livewire 4, Pest 4, Playwright CLI, Chromium.

## Global Constraints

- Work only on `main` and preserve unrelated staged, unstaged, and untracked work.
- Do not change or reconstruct the immutable forum source prompt.
- Do not add a second JavaScript framework or a production browser dependency.
- Run Laravel runtime commands as `www` and keep secrets out of command output.
- Keep all browser artifacts under `output/playwright/auth-runtime-hardening/`.

---

### Task 1: Define The Runtime Artisan Boundary

**Files:**
- Create: `scripts/artisan-runtime`
- Create: `tests/Feature/Runtime/RuntimeArtisanCommandTest.php`
- Modify: `docs/deployment.md`

**Interfaces:**
- Consumes: optional `PAWCIRCLE_RUNTIME_USER`, repository root, and Artisan arguments.
- Produces: `scripts/artisan-runtime <artisan arguments>` with the wrapped process exit code.

- [ ] **Step 1: Write the failing process test**

Create a Pest test that asserts the wrapper exists, is executable, and returns
the installed framework version when invoked with `--version`.

```php
<?php

declare(strict_types=1);

use Symfony\Component\Process\Process;

test('runtime artisan wrapper executes Laravel as the service account', function () {
    $script = base_path('scripts/artisan-runtime');

    expect(is_file($script))->toBeTrue()
        ->and(is_executable($script))->toBeTrue();

    $process = new Process([$script, '--version'], base_path());
    $process->run();

    expect($process->isSuccessful())->toBeTrue($process->getErrorOutput())
        ->and(trim($process->getOutput()))->toStartWith('Laravel Framework 13.');
});
```

- [ ] **Step 2: Verify RED**

Run:

```bash
runuser -u www -- php artisan test --compact tests/Feature/Runtime/RuntimeArtisanCommandTest.php
```

Expected: failure because `scripts/artisan-runtime` does not exist.

- [ ] **Step 3: Implement the wrapper**

Resolve the repository root and runtime account without `eval`. Execute Artisan
directly as the runtime account, delegate only root through `runuser`, and fail
for every other caller.

```bash
#!/usr/bin/env bash

set -euo pipefail

script_directory="$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")" && pwd -P)"
repository_root="$(cd -- "${script_directory}/.." && pwd -P)"
runtime_user="${PAWCIRCLE_RUNTIME_USER:-www}"
php_binary="${PAWCIRCLE_PHP_BINARY:-$(command -v php)}"

if ! id -u "${runtime_user}" >/dev/null 2>&1; then
    printf 'PawCircle runtime user does not exist: %s\n' "${runtime_user}" >&2
    exit 69
fi

current_user="$(id -un)"
cd "${repository_root}"

if [[ "${current_user}" == "${runtime_user}" ]]; then
    exec "${php_binary}" artisan "$@"
fi

if [[ "$(id -u)" -eq 0 ]]; then
    exec runuser -u "${runtime_user}" -- "${php_binary}" artisan "$@"
fi

printf 'Run Artisan as %s or root; current user is %s.\n' \
    "${runtime_user}" "${current_user}" >&2
exit 77
```

- [ ] **Step 4: Document deployment use**

Replace production `php artisan` examples with `scripts/artisan-runtime` and
record the ownership smoke.

Use `scripts/artisan-runtime migrate --force` and
`scripts/artisan-runtime optimize` in the release sequence. State that direct
root-owned Artisan execution is prohibited, and add the exact ownership check:

```bash
find storage bootstrap/cache -type f ! -user www -print
```

- [ ] **Step 5: Verify GREEN and root delegation**

Run:

```bash
runuser -u www -- php artisan test --compact tests/Feature/Runtime/RuntimeArtisanCommandTest.php
scripts/artisan-runtime view:cache
find storage/framework/views -maxdepth 1 -type f ! -user www -print
```

Expected: the test passes, cache warming succeeds, and the final command prints
nothing.

- [ ] **Step 6: Protect the test database from deployed config cache**

Add a runtime regression test for `APP_ENV=testing`, SQLite `:memory:`, and a
temporary storage path. Set PHPUnit's `APP_CONFIG_CACHE` to the non-deployed
`bootstrap/cache/testing-config.php` path and give direct invocations a `/tmp`
storage default. Make the canonical runner allocate and remove a unique
per-process storage directory. Prove each assertion fails before its boundary,
passes afterward, and leaves the production database structurally valid.

### Task 2: Install And Exercise Chromium

**Files:**
- Create runtime artifacts only under: `output/playwright/auth-runtime-hardening/`

**Interfaces:**
- Consumes: connected production URL and Playwright CLI wrapper.
- Produces: sanitized screenshots plus observed console and network evidence.

- [ ] **Step 1: Install the supported Chromium browser**

Run:

```bash
bash /root/.codex/skills/playwright/scripts/playwright_cli.sh install-browser chromium
```

- [ ] **Step 2: Verify registration toggles**

Open `/register`, snapshot before every referenced interaction, verify both
password inputs remain independent, resize to 320 pixels, and capture a
screenshot.

- [ ] **Step 3: Verify invalid-login feedback**

Open `/login`, fill a missing account and wrong password, submit, verify the
localized safe error, inspect the Livewire response and console, and capture a
screenshot without retaining the entered password.

### Task 3: Run Release Evidence

**Files:**
- Verify all files from Tasks 1 and 2 plus the password-visibility slice.

**Interfaces:**
- Consumes: completed runtime boundary and browser smoke.
- Produces: current release evidence and an attributable diff.

- [ ] **Step 1: Run targeted tests and formatting**

```bash
runuser -u www -- php artisan test --compact tests/Feature/Runtime/RuntimeArtisanCommandTest.php tests/Feature/Auth/AuthenticationTest.php tests/Feature/IconSystemContractTest.php
vendor/bin/pint --test
PAO_DISABLE=1 vendor/bin/phpstan analyse --memory-limit=1G --no-progress
```

- [ ] **Step 2: Run assets and cache smokes**

```bash
runuser -u www -- npm run build
scripts/artisan-runtime view:cache
```

- [ ] **Step 3: Run repository gates**

```bash
runuser -u www -- php scripts/run-tests.php --compact
php scripts/preserve-forum-source-prompt.php --check
php scripts/generate-forum-requirements.php --check
```

Report the external forum-history gate exactly if it remains unavailable; do
not weaken or falsify it.

- [ ] **Step 4: Review the attributable diff**

Run `git diff --check`, inspect every changed line, confirm runtime artifacts
are ignored, and prepare only the files owned by this work.
