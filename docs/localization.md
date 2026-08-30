# Localization

## Locales

- `en`: source and fallback locale
- `lt`: Lithuanian
- `ru`: Russian

Locale values are allow-listed. User preference is stored on the account; a
safe session preference may be used before login. Dates and times also use the
user's validated timezone.

## Translation Architecture

Use Laravel language files under `lang/{locale}`. Current files are:

- `auth.php`
- `validation.php`
- `messages.php`
- `places.php`
- `presentation.php`
- `ui.php`
- domain catalogues including `forum_categories.php`, `forum_moderation.php`,
  `forum_groups.php`, `forum_polls.php`, `knowledge.php`, `taxonomy.php`, and
  `animal_taxonomy.php`

`ui.php` contains mechanically extracted static interface text.
`messages.php` contains complete action, HTTP, Livewire, and service messages.
`presentation.php` contains reviewed placeholder/plural templates used to
compose dynamic output without grammatical fragments.

Do not add a parallel database or JavaScript translation system. JavaScript
receives only the explicit translated strings it needs from prepared data or a
small safe bootstrap payload.

## Contributor Workflow

1. Search for an existing stable key.
2. Add/update the key in all three locales.
3. Keep placeholders identical.
4. Use pluralization for counts rather than sentence concatenation.
5. Format dates, numbers, currency, lists, and measurements through a
   locale-aware formatter.
6. Run localization tests and render one critical page per locale.
7. Record a human-review note if a translation is structurally valid but needs
   native-language review.

Never ship the raw key as normal visible content.

## Readable Translation Key Contract

`messages.php` and `ui.php` use readable lower-snake-case keys. A translation
key must not end in a generated digest, random token, timestamp, or opaque
counter. Existing readable keys are stable contracts even when the English
copy is edited later.

`scripts/Support/ReadableTranslationKey.php` is the single normalization
boundary used by both literal-localization scripts. It reuses an existing key
only when the complete source value matches, including intentional prefix
spacing. New unambiguous text receives a readable key capped at a word
boundary. A normalized collision stops with an actionable error; the
contributor must add a deliberate semantic key such as `*_sentence`,
`*_question`, `*_label`, `*_prefix`, or `*_lowercase`.

The repository-wide migration is retained as a permanent non-mutating ratchet:

```bash
php scripts/migrate-readable-translation-keys.php --check
```

Architecture tests also reject digest-suffixed catalogue keys, static
`messages.*` or `ui.*` references, and SHA-derived key generation. Do not
replace those failures with another automatic disambiguator.

Operator-only diagnostic messages written to a logger are not interface copy
and remain stable untranslated log events. The PHP localizer recognizes direct
logger arguments and values whose next use is the first logger argument; it
must not exempt similarly named domain methods or messages returned to users.

## Forum Platform Definitions

Forum category, topic-type, report-reason, moderation-action, appeal-state,
trust-level, reputation-dimension, badge, and community-animal-group records
store stable language-independent keys. Every stored translation key must
resolve in `en`, `lt`, and `ru`.

The 44 system root categories have reviewed names and descriptions in all
three locales. Subcategories retain their immutable source names until a
reviewed locale-specific value exists; the category tree and root selector
ignore unreviewed target-locale values, prefer a reviewed configured fallback,
and finally use the immutable server fallback. Subcategories have no source
description, so an empty description is intentional rather than a missing
translation. Category tree caches are locale-and-audience scoped as
`forum:category-tree:v4:locale:{locale}:audience:{guest|member|admin}`;
synchronization and category mutations invalidate every supported locale and
audience so restricted definitions cannot cross an authorization boundary.

Forum notifications are materialized in the recipient's validated locale.
Notification writers pass that locale directly to Laravel's translator and
must not rely on the request actor's locale or mutate the global application
locale.

## User Content And Guide Translations

User-generated prose remains in the language supplied by its author. A
community guide translation is a separate `KnowledgeArticle`; it never
rewrites or hides the source article. New translations record:

- `translated_from_article_id`;
- `translated_by_user_id`;
- controlled `translation_source`;
- the existing stable translation family and target locale.

The connected translation editor copies safe scope and source metadata, but
starts title, summary, body, and protected-section prose empty. The source ID
is a locked Livewire value and is reloaded and authorized server-side.
Private, draft, and group-scoped source material is not exposed to an
unauthorized translator. Published translations show public-safe source and
translator attribution and retain independent versions and correction
history.

There is no automatic translation service. If one is introduced later, it
must preserve the original, label its source, require human review, and follow
the repository's AI/privacy requirements.

## Taxonomy Names

Scientific names are immutable source data and are never passed through the
translation catalogue. `LocalizedTaxonName` resolves display names in this
order:

1. verified preferred common name for the current locale;
2. another verified current-locale common name;
3. verified common name for the configured fallback locale;
4. the exact active scientific name;
5. the localized unidentified-animal label only when no scientific identity
   exists.

Unverified names may remain searchable source evidence but cannot silently
become the preferred displayed fact. The reusable Livewire selector loads
only bounded matching rows plus current/fallback verified names; it never
hydrates the full taxonomy tree.

## Persistent Group Catalogue

`lang/{en,lt,ru}/forum_groups.php` owns group visibility, status, role,
membership/invitation state, field, action, feedback, event, validation,
privacy, empty, loading, and management text. System group names and
descriptions use stable translation keys. User-authored names, descriptions,
rules, questions, and answers remain in their original locale.

Scientific names are rendered from taxonomy source data and are not
translated. The usual English fallback and placeholder-parity rules apply.

`lang/{en,lt,ru}/forum_polls.php` owns group-content section labels, event and
poll modes, eligibility/result/voter visibility, private-file notices,
medical/legal/scientific authority boundaries, validation, empty states, and
action feedback. User-authored announcements, poll questions/options, event
details, topic titles, guide summaries, and file descriptions remain in the
language supplied by their author.

## Current Translation Quality

English is the source language and fallback. Lithuanian and Russian catalogues
maintain complete key/placeholder parity. The system root-category names and
descriptions, moderation reasons/actions/appeal states, animal community
groups, and this package's guide/taxonomy interface text have explicit
localized wording. Other mechanically extracted legacy catalogue entries can
still contain English source wording pending native-speaker review; the
repository does not claim complete linguistic review of every legacy sentence.

Source-authored fixture/catalogue content such as demo post bodies, names, and
locations is translated through stable keys where it is rendered as
first-party content. Dynamic sentences use complete templates rather than
translated fragments. `I18N-002` and `I18N-004` are enforced by architecture
and rendering tests.

## Formatting

- Store money in minor units; format with locale and currency.
- Store normalized time; render in user timezone and locale.
- Preserve exact device/model/version/serial/error/Wi-Fi identifiers.
- Convert display units without losing original precision.
- Do not concatenate translated sentence fragments.

## Tests

- key parity across locales;
- placeholder parity;
- pluralization;
- fallback;
- localized validation and auth feedback;
- date/time/currency/measurement formatting;
- critical Blade and Livewire rendering in every locale;
- every seeded forum definition key in every locale;
- recipient-locale notification materialization;
- user-guide source preservation, provenance, and private-source denial;
- verified common-name fallback and scientific-name invariance;
- locale-scoped category cache invalidation;
- no untranslated raw key on critical pages.

Architecture gates:

```bash
php scripts/localize-blade-literals.php --check
php scripts/localize-php-messages.php --check
php scripts/migrate-readable-translation-keys.php --check
php artisan test --compact tests/Feature/Forum/ForumMultilingualBehaviorTest.php tests/Feature/LocalizationTest.php tests/Feature/ArchitectureComplianceTest.php
php artisan view:cache
```

## Forum Journal Catalogue

`lang/{en,lt,ru}/forum_journals.php` owns journal types, lifecycle and
collaboration states, metric labels/units, forms, errors, notices, progress
text, media, export, and archive copy. Shared navigation keys remain in
`forum.php`.

All three catalogues must have key and placeholder parity. Journal user prose,
captions, and comments remain in their original language. Metric keys and
stored units are stable identifiers; only their labels are translated.
## Event Catalogue

Platform event text lives in matching `lang/en/forum_events.php`,
`lang/lt/forum_events.php`, and `lang/ru/forum_events.php` catalogues. Enums
resolve labels through stable keys; dates, times, counts, and money use locale
formatters or plural-aware translation calls. Stored user event content
retains its original locale. Scientific names linked through event taxa are
never translated.

## Expert Session Catalogue

Platform session text lives in matching
`lang/en/forum_expert_sessions.php`,
`lang/lt/forum_expert_sessions.php`, and
`lang/ru/forum_expert_sessions.php` catalogues. They own scope labels,
lifecycle/moderation states, queue/answer/correction/report forms, errors,
feedback, loading/offline text, accessibility labels, and the versioned
medical/legal disclaimer.

Stored scope, jurisdiction, status, reason, and disclaimer-version values are
stable identifiers. User questions, answers, and correction prose remain in
their original language.

## Topic Lifecycle Catalogue

`lang/{en,lt,ru}/forum_topic_lifecycle.php` owns canonical and legacy state
labels, event labels, request kinds/states, warnings, controls, fields,
validation, confirmations, feedback, and audit-reason explanations.

Stored status, event, kind, reason-code, and idempotency values remain stable
language-independent identifiers. Topic, request, proposal, and private hold
prose are retained in the language supplied by their author. All three
catalogues have tested key and placeholder parity.
