# Requirements Catalogue

## Authority

Active requirements are defined in:

- `docs/product-requirements.md` for functional behaviour.
- `docs/system-requirements.md` for application and data contracts.
- `docs/non-functional-requirements.md` for security, quality, accessibility,
  localization, performance, and operations.
- `docs/requirements/laravel-engineering-standard.md` for stable `LAR-*`
  engineering rules.
- Applicable feature specifications linked from `docs/index.md`.

The compliance matrix is traceability evidence, not a substitute for these
statements.

## Stable Identifier Families

| Prefix | Domain |
| --- | --- |
| `PRD-IDENTITY` | Accounts, owner identities, and pet profiles |
| `PRD-SOCIAL` | Feed, connections, groups, events, and messaging |
| `PRD-FORUM` | Forum and knowledge |
| `PRD-EXPERT` | Expert profiles, bookings, and consultations |
| `PRD-MARKET` | Listings, reservations, orders, reviews, and disputes |
| `PRD-SEARCH` | Lost and found coordination |
| `PRD-MEDICAL` | Private medical records |
| `PRD-CARE` | Private care journals |
| `PRD-DEVICE` | Smart devices and automation |
| `PRD-PLACE` | Places and maps |
| `SYS-*` | Application, API, files, cache, and runtime behaviour |
| `SEC-*` | Security and privacy controls |
| `DATA-*` | Schema and data-integrity contracts |
| `PERF-*` | Performance budgets |
| `UI-*` | Responsive and accessible interface |
| `I18N-*` | Localization |
| `TEST-*` | Automated verification |
| `SEED-*` | Factories and seed data |
| `OPS-*` | Deployment and operations |
| `LAR-*` | Laravel engineering standards |

Identifiers are never renumbered for presentation convenience. A retired
identifier remains documented with a superseded reason.

## Requirement Record Contract

Every active requirement has:

- an identifier and testable statement;
- rationale where the reason is not obvious;
- affected roles and entities;
- authorization and validation rules;
- success and failure behaviour;
- security and data implications;
- localization and accessibility implications;
- a performance expectation;
- implementation and test locations;
- one controlled status in the compliance matrix.

Controlled statuses:

- `implemented and verified`
- `implemented`
- `partially implemented`
- `blocked by external dependency`
- `not applicable`
- `superseded`

Only a passing relevant check permits `implemented and verified`.

## Feature Source Preservation

The earlier feature specifications contain granular ledgers such as `P8-*` and
`MAP-*`. Those identifiers remain stable. Their session-backed implementation
claims describe superseded prototype history only. Production identity,
authorization, localization, and persistence requirements in the canonical
catalogue apply instead.
