# Community Current Progress

Last updated: 2026-08-01

Status: incomplete; 35 community IDs are verified and 3,541 remain open.

## Current State

The exact Point 6 source is preserved and deterministically expanded into
3,576 `community.*` atoms across phases 55-63. The combined catalogue contains
26,093 unique requirements and retains the exact ordering of the previous
22,517 IDs.

The repository audit and architecture decisions are complete. The first phase
55/56 production slice is implemented: community membership now stores both
the real account and selected participation profile, supports distinct
personal and pet memberships for one account, permits eligible expert
profiles, applies a per-community actor-type allowlist, records accepted rules
version/time, and rechecks actor control on the server for joins and invitation
acceptance.

## Implemented Files

- additive migration for rules metadata and profile-scoped memberships;
- `CommunityMembershipActorEligibility` as the reusable policy boundary;
- profile-aware membership model relationships, Actions, and policy checks;
- class-based Livewire profile selection and EN/LT/RU labels;
- factory and seeder compatibility for accountable personal actors;
- focused schema, rollback, tampering, multiplicity, rules, and Livewire tests.

## Verification

- `CommunityProfileMembershipTest`: 8 tests and 38 assertions;
- related social and content regression set: 52 tests and 736 assertions;
- final clean serial repository suite: 2,015 tests and 72,245 assertions;
- Pint and Larastan: pass, with zero static-analysis errors;
- isolated SQLite `migrate:fresh --seed`, default migrate, and default seed: pass;
- Composer and npm audits: no known vulnerabilities;
- Vite production build and Laravel config, event, route, view, and icon caches:
  pass;
- Chrome desktop, mobile, and Russian community checks: pass with no overflow,
  unnamed controls, duplicate IDs, invalid images, raw translation keys,
  undersized touch targets, or console errors.

Deterministic preservation and generation checks pass for source SHA-256
`408fa2f6cd8d5189f05ebe3762df42a332221f8ee6a83ed366dbc4ec54d2ec8e`
and 26,093 atomic requirements.

## Open Boundary

This is not completion of Point 6. Organization/shelter actors, chapter and
subgroup hierarchy, custom/temporary roles, immutable rule history,
community-specific knowledge curation, volunteering, finance, advanced
moderation and anti-raid operations, discovery controls, full lifecycle
workflows, offline behavior, and the complete scenario matrix remain open in
phases 55-63.
