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
- domain catalogues including `forum_groups.php` and `forum_polls.php`

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
currently maintain complete key/placeholder parity but mostly contain the
English source wording pending native-speaker translation. This is deliberate:
the interface resolves stable keys and never breaks or exposes raw identifiers,
but the repository does not claim those two catalogues have completed
linguistic review.

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
- no untranslated raw key on critical pages.

Architecture gates:

```bash
php scripts/localize-blade-literals.php --check
php scripts/localize-php-messages.php --check
php artisan test --compact tests/Feature/LocalizationTest.php tests/Feature/ArchitectureComplianceTest.php
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
