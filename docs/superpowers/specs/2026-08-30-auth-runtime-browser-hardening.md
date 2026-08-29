# Authentication Runtime And Browser Hardening

## Goal

Keep Laravel's writable runtime files owned by the PHP-FPM service account and
verify the complete authentication interaction in a real Chromium browser.

## Runtime Contract

- Production PHP-FPM runs as `www`.
- Repository-managed Artisan commands run through `scripts/artisan-runtime`.
- When invoked by `root`, the wrapper delegates directly to `www` with
  `runuser`; when invoked by `www`, it executes Artisan directly.
- A different unprivileged user is denied by repository permissions or the
  wrapper instead of creating mixed-owner runtime files.
- The runtime account may be overridden only through the operator-scoped
  `PAWCIRCLE_RUNTIME_USER` environment variable.
- The wrapper changes no application configuration, credentials, database
  semantics, or authorization behavior.
- PHPUnit resolves a dedicated non-deployed `APP_CONFIG_CACHE` path, so a
  production config cache cannot override the testing environment or replace
  the configured in-memory SQLite database.
- PHPUnit resolves `LARAVEL_STORAGE_PATH` outside the repository. The canonical
  test runner assigns a unique temporary storage directory per process and
  removes it after the suite, preventing test views and filesystem fakes from
  changing production runtime ownership.

## Deployment Contract

Production migrations, cache warming, and documented application backfills use
the runtime wrapper. Composer and frontend dependency/build commands remain
operator-owned because they write outside Laravel's runtime directories.

`storage` and `bootstrap/cache` must be writable by `www` before deployment.
The release smoke rejects root-owned compiled views after cache warming.

## Browser Verification Contract

A connected Chromium smoke covers `/register` and `/login` at desktop and
mobile widths. It verifies:

- registration renders two independently controlled password fields;
- each button changes only its own input between masked and visible states;
- accessible names and `aria-pressed` values change with the state;
- invalid login credentials render the localized safe error without a page
  navigation or password disclosure;
- the browser console contains no errors.

Chromium is an operator dependency for the connected smoke and is not added to
the application's production dependency graph.

## Forum Source-History Boundary

`docs/requirements/forum-source-prompt.md` is immutable. The current forum
preservation check requires ten timestamped records from external Codex
history, and the repository documents that those records are unavailable in
this environment. This hardening work must not synthesize, rewrite, or weaken
that evidence gate. The deterministic requirements generator remains runnable
independently.

## Verification

- A focused Pest process test proves the wrapper resolves the runtime user and
  runs Artisan successfully.
- A focused isolation test proves Pest boots in `testing` with SQLite
  `:memory:` and temporary storage even while the deployed configuration cache
  exists.
- A root-invoked cache warm proves the resulting compiled views belong to
  `www`.
- Auth and icon feature tests, Pint, Larastan, production Vite build, and live
  HTTP smokes remain green.
- Playwright CLI records sanitized screenshots under
  `output/playwright/auth-runtime-hardening/`; console and network results are
  reported without retaining form-value snapshots.
