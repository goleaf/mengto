# Localization Audit Work Ledger

Date: 2026-08-30

## Protected Baseline

- Branch: `main`.
- Starting commit: `9540fe8` (`docs: register readable translation key delivery`).
- Upstream relationship at audit start: `main...origin/main [ahead 3]`.
- The checkout was already materially dirty, with staged, unstaged, and
  untracked changes across application, translation, test, generated-evidence,
  and documentation paths before this delivery began.
- Every pre-existing hunk remains user-owned. Analysts are read-only. The
  principal owns all edits, integration decisions, verification, an
  attributable temporary index, commit, and push.

## Analyst Ledger

| ID | Required agent | Exclusive read-only scope | Structured deliverable | Status |
| --- | --- | --- | --- | --- |
| LC15-A1 | Locale Architecture and Routing Analyst | `config`, locale middleware, bootstrap, routes, `User` preference, session/cookie persistence, language/JSON catalogues, database-translated fields, and HTTP/Livewire/mail/notification/API/job locale flow | locale architecture map; canonical locales/fallback; route, persistence, invalid-locale, tenant/content-locale and RTL findings with exact paths and tests | assigned wave 1 |
| LC15-A2 | Hardcoded String Scanner | PHP/Blade/Livewire/JavaScript user-facing literals, a11y names, exceptions and API responses; scanner scripts and false-positive boundaries | classified literal inventory with exact locations, severity, replacement keys, intentional exclusions | assigned wave 1 |
| LC15-A3 | Translation Consistency Agent | complete `lang/en`, `lang/lt`, `lang/ru` trees and JSON files; key nesting, placeholders, plural syntax, duplicate values, fallback and raw keys | parity/mismatch report plus human-review candidates and deterministic checks | assigned wave 1 |
| LC15-A4 | Validation and Notification Translation Agent | Form Requests, Livewire forms, domain exceptions, notifications, mailables/templates, user-facing API errors and deferred recipient locale | communication matrix; defects, required keys, recipient-locale behavior and focused tests | queued wave 2 |
| LC15-A5 | Formatting and Pluralization Agent | presentation formatters and every date/time/relative/number/currency/percentage/list/measurement/count sink in PHP, Blade and JS | ownership map, direct-format/fragment defects, locale/timezone edge cases and tests | queued wave 2 |
| LC15-A6 | Localized Content and SEO Analyst | public pages, presenters/SEO builders, locale routes, slugs, database translations, canonical/alternate metadata, Open Graph, JSON-LD, authored-content boundaries and tests | localized public-content matrix; fallback/indexing/escaping defects; applicable and not-applicable SEO decisions with required tests | queued wave 2 |
| LC15-A7 | Localization Test and Automation Analyst | localization/architecture/render/browser tests, scanners, factories, seeders, critical routes and communications, fallback/switch/persistence, long text, Unicode and RTL applicability | coverage matrix, missing RED contracts, automation/check-script changes, deterministic fixtures and exact commands | queued wave 3 |

Agents must not edit files, stage changes, run destructive database commands,
or change shared persistent state. Reports distinguish confirmed defects from
candidate literals, authored content, diagnostics, identifiers, fixtures, and
other intentional nonlocalized values.

## Reviewer Ledger

| ID | Required reviewer | Frozen scope | Status |
| --- | --- | --- | --- |
| LC15-R1 | Translation Coverage Reviewer | final attributable source/catalogue/scanner/test diff | blocked until implementation freeze |
| LC15-R2 | Locale Behaviour Reviewer | locale selection, persistence, fallback, formatting, pluralization and recipient-locale behavior | blocked until implementation freeze |
| LC15-R3 | Localization Regression Reviewer | critical EN/LT/RU journeys, long text, a11y/SEO output and responsive browser evidence | blocked until implementation freeze |

## Disposition Contract

Every analyst and reviewer finding is recorded as accepted, rejected with
evidence, duplicate, out of scope, or blocked. Accepted findings receive a RED
test where behavior is testable, the smallest coherent fix, GREEN evidence,
and repeated affected checks. No requirement status is promoted from intended
or implemented to verified without a freshly observed passing command.
