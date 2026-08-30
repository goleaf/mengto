# Readable Translation Keys Design

## Problem

The `messages` and `ui` catalogues contain 5,860 mechanically generated keys
ending in a ten-character SHA-256 fragment, for example
`legacy_question_retained_for_moderation_review_1c47951f44`. The suffixes are
not meaningful to contributors, make references difficult to review, and are
created deliberately by both first-party localization scripts.

The defect is repository-wide rather than limited to the four observed Place
compatibility fallbacks:

- `lang/en/messages.php` contains 3,502 hash-suffixed keys;
- `lang/en/ui.php` contains 2,358 hash-suffixed keys;
- the matching Lithuanian and Russian catalogues retain the same key sets;
- PHP, Blade, tests, and catalogue contracts reference those keys across the
  application.

## Decision

Every hash-suffixed key in the `messages` and `ui` catalogues will be replaced
with a readable lower-snake-case key derived from the normalized complete
English source text. Keys may be capped at 160 characters at a word boundary,
but they must never receive a hash, random token, opaque counter, or other
machine-only disambiguator.

Existing non-hashed keys are stable contracts and will not be renamed merely
to match the mechanical naming rule. Translation values and placeholders in
`en`, `lt`, and `ru` remain attached to the same call sites. Whitespace-only
duplicate source values are consolidated only after confirming that their
rendering does not rely on translated fragments.

Normalization can expose distinct source strings with the same readable key,
such as sentence-case and lower-case labels or a label with and without final
punctuation. The migration owns an explicit reviewed override map for these
cases. Override names use semantic qualifiers such as `label`, `sentence`,
`question`, `lowercase`, `hyphenated`, or `with_comma`; they never use a digest
or unexplained number.

## Generation Contract

A shared first-party helper will own readable key normalization for both
localization scripts. Each script follows this order:

1. normalize the candidate English text for lookup;
2. reuse an existing catalogue key when that text already exists;
3. derive a readable key for a new unambiguous value;
4. stop with an actionable collision error when another value owns that key.

The collision error directs the contributor to add a deliberate catalogue key
for the new text. The scripts may never fall back to a digest, random suffix,
timestamp, or numeric sequence.

## Migration

A deterministic migration command will:

1. load `lang/{en,lt,ru}/{messages,ui}.php` and verify locale key parity;
2. build one old-to-new mapping from the English catalogues and the explicit
   collision overrides;
3. reject duplicate targets with different source or translated values;
4. replace exact translation-key references in first-party PHP, Blade, and
   test files;
5. rewrite all three locale catalogues while preserving the mapped values;
6. sort the rewritten catalogues and report old/new/reference counts;
7. support a non-mutating `--check` mode that fails if migration remains.

The migration is source-only. It changes no database identifiers, user prose,
stored translation keys in domain tables, or generated forum evidence.

## Permanent Ratchet

Architecture coverage will reject:

- any `messages` or `ui` catalogue key ending in `_[0-9a-f]{10}`;
- any first-party `messages.*` or `ui.*` reference ending in that pattern;
- any localization-script implementation that derives a key from a digest;
- locale key or placeholder drift caused by the rename.

Focused tests will exercise readable normalization, existing-key reuse,
collision failure, migration check mode, catalogue parity, and the four Place
compatibility fallbacks that exposed the defect.

## Documentation And Delivery

The canonical implementation plan, localization workflow, testing guidance,
compliance evidence, and changelog will describe the readable-key contract.
The complete change will be verified with targeted tests first, then Pint,
Larastan, the serial full suite, dependency checks, build, cache smokes, and
the applicable browser/localization checks. Publication occurs only if the
required observed gates are green; otherwise the exact blocker remains
reported without promoting verification status.

## Rollback

Rollback is a normal revert of the coherent migration commit. No database
rollback, destructive data operation, or history rewrite is required.
