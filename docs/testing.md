# Testing

## Framework

Pest 4 is the primary PHP framework. PHPUnit 12 is its compatible underlying
runner and is not a second test style.

## Test Layers

- Feature: routes, middleware, auth, policies, validation, persistence,
  downloads, integrations, and rendered pages.
- Unit: pure state transitions, value objects, normalization, and safety rules.
- Livewire: component state, forms, direct action authorization, repeated
  submission, events, redirects, and rendered states.
- Policies: every method across all 13 policies is exercised for applicable
  owner/participant, outsider, administrator, blocked, guest, relationship, and
  resource-state cases.
- Browser: browser permissions, media teardown, focus/modal behaviour,
  responsive overflow, maps, and repeated navigation.
- Architecture: repository constraints.

## Required Commands

Targeted:

```bash
php artisan test --compact tests/Feature/RelevantTest.php
vendor/bin/pint --dirty
PAO_DISABLE=1 vendor/bin/phpstan analyse --memory-limit=1G
```

Final:

```bash
composer validate --strict
composer audit
vendor/bin/pint --test
PAO_DISABLE=1 vendor/bin/phpstan analyse --memory-limit=1G
php artisan test --compact
php artisan test --parallel
php scripts/verify-fresh-database.php
npm audit --audit-level=high
npm run build
```

Parallel tests run only when database isolation is safe.

## Coverage

Meaningful branch coverage is mandatory for authentication, authorization,
tenant/owner isolation, tokens, payment/order transitions, device commands,
medication doses, and care idempotency. The total application target is at
least 90% when a supported coverage driver is installed.

Do not write assertion-free tests or exclude difficult first-party code to
inflate the percentage. If no coverage driver exists, record the exact
environmental blocker while retaining all other behavioural tests.

## Fakes And Isolation

- `RefreshDatabase` for database-integrated tests.
- Factories for records.
- `Http::fake()` and stray-request prevention.
- `Storage::fake()` for file lifecycle.
- Notification, mail, event, and queue fakes only where observing the fake
  preserves the behaviour being tested.
- Temporary SQLite path for destructive migration/seeder checks.

Never run `migrate:fresh` against the local or production database as a test.
`scripts/verify-fresh-database.php` creates an operating-system temporary
SQLite file, asserts that it is outside the repository, and only then runs the
destructive command.

## Baseline

The modernization baseline was 116 passing tests and 3,881 assertions. The
final complete serial and parallel runs both report 696 passing tests and
31,698 assertions after removing two meaningless framework example tests.

Pest coverage cannot run in the current environment because PHP 8.5 has neither
PCOV nor Xdebug. The expected failing command and its exact reason remain part
of final evidence:

```bash
php artisan test --coverage --min=90 --compact
```
