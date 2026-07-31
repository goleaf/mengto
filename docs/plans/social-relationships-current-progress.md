# Social Relationships Current Progress

Last updated: 2026-07-31

## Current Phase

Phase 26 preservation, atomization, repository audit, gap analysis, and
architecture decisions are complete. The Phase 27/28 foundation and the
account-block/request-safety package are implemented, release-verified, and
represented by conservative per-ID evidence overlays. Later social packages
remain open.

## Completed Evidence

- Source timestamp `1785521058` preserved verbatim with revision SHA-256
  `5455fc185c1348ac7233d35ec18285b850c19e0bb28cbda2dc90eeb87bc6276d`.
- Combined source checksum is
  `ad88d55de0faf7d5fe62c97479be42f6539316a13eeae9d2bbfd8a6b3716c32d`.
- 3,210 new atomic requirements generated across 18 social domains and phases
  27-34; all previous 11,419 IDs remain unchanged.
- Existing prototype state, routes, Actions, identities, policy boundaries,
  schema, tests, and production constraints audited.
- Foundation requirement intervals, desired result, schema, migration,
  rollback, compatibility, authorization, privacy, interface, tests, and
  acceptance criteria recorded before production edits.
- Additive actor, setting, request, relationship, and immutable-event tables
  implemented with foreign keys, bounded list indexes, active-edge uniqueness,
  global idempotency keys, and reversible rollback.
- Canonical adapters implemented for users, pets, experts, and groups; the
  shared bounded backfill powers both CLI migration support and demo seeding
  while retaining encrypted prototype state without inventing consent.
- Policy-checked Actions now cover public/private follows, owner/pet friendship
  requests, accept/decline/cancel/expiry, end, close circle, mute, restriction,
  block, settings, cache invalidation, and immutable real-user audit.
- Class-based Livewire `/circle/social` relationship center implemented with
  actor switching, bounded safe directory search, request handling, active
  relationship controls, privacy settings, and EN/LT/RU text.
- Focused evidence: 22 tests and 432 assertions; the expanded pet/social and
  architecture slice passed 63 tests and 26,709 assertions; the schema,
  factory, and social regression passed 1,250 tests and 4,055 assertions; the
  final serial repository suite passed 1,861 tests and 69,718 assertions in
  90.930 seconds.
- Isolated migration/seed/repeat-backfill and populated rollback/re-application
  preserved profile counts. Pint, Larastan, Composer/npm audits, Vite, cache
  compilation, source checks, and deterministic requirement generation pass.
- Browser verification passed English desktop/mobile/320px and Russian social
  pages with zero overflow, unnamed-control, touch-target, raw-key, or console
  findings.
- Account-wide blocks now cover all current and future user, pet, expert, and
  group actors controlled by the blocked account while preserving pet-care
  roles; unblock never restores ended contacts.
- Request context is normalized, encrypted, bounded, screened for direct
  contact details and repeated templates, and protected by real-account
  rolling, new-account, and low-acceptance limits.
- Recipients can decline, permanently stop repeats, block an account, and file
  an idempotent private report from the Livewire request card. Critical actions
  have confirmation and full actor-specific accessible names in EN/LT/RU.
- Safety evidence: 8 focused tests and 65 assertions; combined social tests 30
  and 593 assertions; architecture/factory/accessibility slice 1,292 tests and
  29,221 assertions; final serial suite 1,872 tests and 70,764 assertions in
  107.953 seconds.
- Fresh verification passed 103 migrations, 183 tables, and repeat seed with
  stable user counts. Composer/npm audits, Pint, Larastan, Vite, and Chrome
  desktop/mobile/320px/Russian checks pass.
- Exactly 222 independently proven `social.*` IDs are verified. The remaining
  social records keep their planned/discovered status.

## Open Work

1. Keep cross-account/device anti-stalking correlation, recommendations,
   messaging, meetings/location, minors, notifications, moderation appeals,
   transfer/deletion/memorial, and public viewer-aware lists in their explicit
   later packages.
2. Start the next package from an exact open-ID slice and retain the same
   migration, policy, browser, and evidence gates.
