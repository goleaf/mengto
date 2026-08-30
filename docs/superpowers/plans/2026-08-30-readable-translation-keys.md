# Readable Translation Keys Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Replace every ten-hex-digest `messages` and `ui` translation key with a readable stable key and permanently prevent digest-based key generation.

**Architecture:** A standalone tested `ReadableTranslationKey` script support class owns text normalization and collision detection. A deterministic migration command maps the two English catalogues, preserves all EN/LT/RU values, rewrites exact first-party references, and exposes `--check`; both literal localizers reuse the same helper and fail closed on ambiguity.

**Tech Stack:** PHP 8.5, Laravel 13 language catalogues, Pest 4, first-party PHP maintenance scripts.

## Global Constraints

- Preserve all unrelated staged, unstaged, and untracked work in the shared `main` checkout.
- Preserve EN/LT/RU catalogue parity and placeholder sets.
- Do not alter database identifiers, stored user prose, generated forum evidence, or immutable source prompts.
- Never generate a digest, random token, timestamp, or opaque numeric disambiguator in a translation key.
- Begin every behavior change with an observed failing test.

---

### Task 1: Readable-key and architecture RED contracts

**Files:**
- Create: `tests/Unit/Support/ReadableTranslationKeyTest.php`
- Modify: `tests/Feature/ArchitectureComplianceTest.php`

**Interfaces:**
- Consumes: English source strings and existing catalogue key/value maps.
- Produces: required `ReadableTranslationKey::fromText(string): string` and `ReadableTranslationKey::resolve(string, array): string` behavior plus the repository-wide no-digest ratchet.

- [x] **Step 1: Write the failing normalization test**

```php
it('creates a readable key without an opaque suffix', function (): void {
    expect(ReadableTranslationKey::fromText(
        'Legacy question retained for moderation review.',
    ))->toBe('legacy_question_retained_for_moderation_review');
});
```

- [x] **Step 2: Write the failing collision test**

```php
it('rejects an ambiguous readable key instead of appending a digest', function (): void {
    expect(fn (): string => ReadableTranslationKey::resolve('about', [
        'about' => 'About',
    ]))->toThrow(RuntimeException::class, 'Add a deliberate translation key');
});
```

- [x] **Step 3: Add the architecture ratchet**

```php
test('translation keys never contain generated digest suffixes', function (): void {
    foreach (['messages', 'ui'] as $catalogue) {
        foreach (['en', 'lt', 'ru'] as $locale) {
            foreach (array_keys(require lang_path("{$locale}/{$catalogue}.php")) as $key) {
                expect($key, "{$locale}.{$catalogue}.{$key}")
                    ->not->toMatch('/_[0-9a-f]{10}$/');
            }
        }
    }
});
```

- [x] **Step 4: Run RED**

Run:

```bash
php scripts/run-tests.php --compact tests/Unit/Support/ReadableTranslationKeyTest.php tests/Feature/ArchitectureComplianceTest.php
```

Expected: failure because the helper does not exist and current catalogues contain 5,860 digest-suffixed keys.

### Task 2: Shared readable-key generator

**Files:**
- Create: `scripts/Support/ReadableTranslationKey.php`
- Create: `scripts/Support/PhpMessageLiteralClassifier.php`
- Modify: `scripts/localize-blade-literals.php`
- Modify: `scripts/localize-php-messages.php`
- Test: `tests/Unit/Support/ReadableTranslationKeyTest.php`
- Test: `tests/Unit/Support/PhpMessageLiteralClassifierTest.php`

**Interfaces:**
- Consumes: normalized English source text, an existing `array<string, string>` catalogue, and optional explicit collision overrides.
- Produces: `fromText`, `resolve`, and actionable `RuntimeException` collision behavior used by both localizers.

- [x] **Step 1: Implement the minimal helper**

```php
final class ReadableTranslationKey
{
    public static function fromText(string $text): string
    {
        $words = preg_replace('/[^\pL\pN]+/u', '_', mb_strtolower(trim($text)));

        return mb_substr(trim((string) $words, '_') ?: 'text', 0, 160);
    }
}
```

Complete it with exact whitespace/entity normalization, word-boundary capping, `pawcircle` to `brand` normalization, existing-value reuse, explicit overrides, and fail-closed collision reporting required by the tests.

- [x] **Step 2: Replace both digest generators**

Require the shared helper from both scripts and replace each SHA-derived key path with `ReadableTranslationKey::resolve(...)`. Keep existing catalogue values and exact-text reuse authoritative.

- [x] **Step 3: Run GREEN for the helper without weakening the catalogue RED**

Run:

```bash
php scripts/run-tests.php --compact tests/Unit/Support/ReadableTranslationKeyTest.php
```

Expected: all helper tests pass; the architecture ratchet still fails until migration.

### Task 3: Deterministic repository-wide migration

**Files:**
- Create: `scripts/migrate-readable-translation-keys.php`
- Modify: `lang/{en,lt,ru}/messages.php`
- Modify: `lang/{en,lt,ru}/ui.php`
- Modify: exact matching references under `app`, `resources`, `routes`, `database`, and `tests`
- Test: `tests/Feature/ArchitectureComplianceTest.php`

**Interfaces:**
- Consumes: the six locale catalogue files, explicit semantic collision overrides, and exact string references.
- Produces: identical locale value maps under readable keys, complete reference rewrites, and `--write` / `--check` exit contracts.

- [x] **Step 1: Implement migration preflight and mapping**

```php
if ($write === $check) {
    fwrite(STDERR, "Choose exactly one of --write or --check.\n");
    exit(2);
}
```

Verify locale parity before mutation. Build old-to-new mappings only for keys matching `_[0-9a-f]{10}$`; use reviewed semantic overrides for every ambiguous target and reject unresolved collisions.

- [x] **Step 2: Implement exact reference and catalogue rewriting**

Rewrite only exact old keys in first-party text files, map every locale value to the same new key, sort catalogue output, and reject value loss, target duplication, or unresolved old references. `--check` must not write.

- [x] **Step 3: Observe migration check RED**

Run:

```bash
php scripts/migrate-readable-translation-keys.php --check
```

Expected: non-zero with 5,860 catalogue keys still requiring migration.

- [x] **Step 4: Execute the migration and inspect counts**

Run:

```bash
php scripts/migrate-readable-translation-keys.php --write
php scripts/migrate-readable-translation-keys.php --check
```

Expected: the write reports exactly 5,860 renamed catalogue keys and the subsequent check exits zero with no remaining digest key or reference.

- [x] **Step 5: Run focused GREEN**

Run:

```bash
php scripts/run-tests.php --compact tests/Unit/Support/ReadableTranslationKeyTest.php tests/Feature/ArchitectureComplianceTest.php tests/Feature/LocalizationTest.php tests/Feature/PageIdentityStandardizationTest.php
```

Expected: all selected tests pass with locale parity and no raw/digest keys.

### Task 4: Documentation, review, and release evidence

**Files:**
- Modify: `docs/implementation-plan.md`
- Modify: `docs/localization.md`
- Modify: `docs/testing.md`
- Modify: `docs/requirements/compliance-matrix.md`
- Modify: `CHANGELOG.md`

**Interfaces:**
- Consumes: observed RED/GREEN and final-gate results.
- Produces: accurate `I18N-KEY-*` statuses and contributor guidance.

- [x] **Step 1: Update documentation from observed evidence**

Document readable-key reuse, manual collision handling, the migration check, architecture ratchet, affected requirement paths, rollback, and only the checks actually observed.

- [x] **Step 2: Run source and formatting gates**

```bash
php scripts/localize-blade-literals.php --check
php scripts/localize-php-messages.php --check
php scripts/migrate-readable-translation-keys.php --check
vendor/bin/pint --test
PAO_DISABLE=1 vendor/bin/phpstan analyse --memory-limit=1G
```

- [x] **Step 3: Run complete release gates**

```bash
composer validate --strict
composer audit
php scripts/run-tests.php --compact
php scripts/verify-fresh-database.php
npm audit --audit-level=high
npm run build
php artisan config:cache
php artisan route:cache
php artisan view:cache
npm run test:browser:a11y
```

Observed on 2026-08-30: the task-owned migration, focused tests, targeted Pint,
Composer validation/audit, NPM audit/build, fresh migration/seed/idempotency, and
cache smokes passed. Both localization checks report zero remaining eligible
literals. Publication remains blocked because concurrent application work leaves
full Pint, Larastan, full Pest, the Place compatibility backfill test, and the
browser community-rules acceptance assertion red.

- [ ] **Step 4: Review and publish the attributable diff**

Inspect the frozen task diff, run `git diff --check`, verify no secrets or old digest references remain, record every material finding and disposition, stage with a temporary `GIT_INDEX_FILE`, commit on `main`, and push only when every required gate is green.
