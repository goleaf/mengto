# EVENT-P13-ADVANCED Work Ledger

Date: 2026-08-30

Status: specialist discovery starting; production code unchanged.

## Preservation Boundary

The task starts on `main` at `ae4ac32`, aligned with `origin/main`. The existing
modification to `docs/implementation-plan.md` and the untracked Places/shared-
directory audit ledgers pre-date this delivery and remain user-owned. Only the
new `EVENT-P13-ADVANCED` section in that shared plan file and the paths recorded
by this ledger are attributable. No contributor may reset, discard, rewrite,
stage, or commit unrelated work. Publication must use a temporary
`GIT_INDEX_FILE` and review its complete diff.

## Shared Rules

- Specialists are read-only during discovery and own one exclusive scope.
- Reports identify exact requirement IDs/sections, current files, missing
  entities/state transitions, reusable authorities, threats, tests, and a
  recommended smallest durable boundary.
- A specialist does not decide another domain's schema and does not edit shared
  production, migration, translation, documentation, or test files.
- The principal reconciles cross-domain keys, shared enums, notification and
  audit boundaries, all edits, and every finding disposition.
- Final review uses a frozen attributable diff. The final reviewer is
  independent from all implementation work.

## Specialist Ledger

| Specialist ID | Exclusive scope | Structured deliverable | Status | Report/disposition |
| --- | --- | --- | --- | --- |
| EVENT-S01 | Competitions: rules, categories, entries, eligibility, judges, scaled scoring, corrections, results, appeals, immutable history | Current-state inventory, normalized entity/state design, constraints/locks/idempotency, public projection, adversarial test matrix | pending | pending |
| EVENT-S02 | Commercial participants: vendor applications/review/areas/contacts and sponsor packages/benefits/disclosures/lifecycle | Reuse map for marketplace/organization/place, state/expiry model, public/private fields, constraints and tests | pending | pending |
| EVENT-S03 | Volunteer staffing: roles/skills/applications/shifts/capacity/assignment/substitution/cancellation/attendance/communication | Entity/state/access design, capacity/concurrency rules, privacy revocation, tests | pending | pending |
| EVENT-S04 | Safety incidents: severity/category/scope/reporter/responder/actions/evidence/resolution/private history | Factual-source model, policy matrix, evidence boundary, escalation/recovery and adversarial tests | pending | pending |
| EVENT-S05 | Weather: configured provider adapter/manual fallback/staleness/no automatic cancellation | Provider/config/DTO design, HTTP/time failure matrix, observation/decision separation, tests | pending | pending |
| EVENT-S06 | Certificates: attendance/achievement source, issue/regenerate/revoke/version/download | Canonical-source mapping, lifecycle/checksum/private-file design, policy and tests | pending | pending |
| EVENT-S07 | Feedback: privacy/moderation/anti-abuse/safe aggregates | Typed feedback and moderation reuse, rate/idempotency rules, threshold query/projection, retaliation/privacy tests | pending | pending |
| EVENT-S08 | Privacy, archive, retention, deletion, legal hold, export, redaction | Data-class/retention schedule, hold precedence, deletion/redaction/export state machine, authorization/audit tests | pending | pending |
| EVENT-R01 | Independent final review of frozen attributable implementation diff | Severity-ranked reproducible findings, requirement mapping, disposition verification and release verdict | pending | pending |

## Requirement Manifest Boundary

Primary domain records are the relevant atoms in `event-competition`,
`event-vendor-sponsor`, `event-volunteer`, `event-safety-incident`,
`event-weather-cancellation`, `event-media-privacy`, and
`event-feedback-archive`. Cross-cutting atoms are selected only when directly
implemented by this delivery from `event-data`, `event-authorization`,
`event-validation`, `event-livewire`, `event-interface`,
`event-localization`, `event-factory`, `event-seeding`, `event-testing`,
`event-performance`, `event-notification-calendar`,
`event-documentation`, and `event-quality-release`. Exact IDs and evidence are
recorded after implementation; no entire domain is promoted from a label.

## Findings And Dispositions

No specialist findings have been returned. The principal will append each
report path, material finding, disposition, implementation mapping, and
affected rerun before `EVENT-ADV-09` can complete.

## Verification Ledger

No production verification has been claimed. Planning checks are limited to
the observed Git baseline, canonical requirement reads, and this attributable
plan/ledger diff.
