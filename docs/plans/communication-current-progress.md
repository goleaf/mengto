# Communication Current Progress

Last updated: 2026-08-01

Status: not started at the atomic evidence boundary; all 3,877 communication
IDs remain open.

## Current State

The complete point 5 source is preserved verbatim in
`docs/requirements/forum-source-prompt.md`. The deterministic generator has
assigned all 3,877 `communication.*` atoms to phases 46-54. Existing requirement
IDs remain stable and the combined catalogue contains 22,517 unique atoms.

Repository audit and architecture decisions are complete. No production
communication requirement is marked verified yet. The current `/messages`
experience remains an encrypted per-user UI prototype, not cross-account
delivery, WebRTC, file scanning, server receipts, or E2EE.

## Phase Counts

- phase 46: 703 dialog, contact, data, and identity requirements;
- phase 47: 503 message, media, file, and sharing requirements;
- phase 48: 528 workflow, delivery, device, archive, and search requirements;
- phase 49: 416 group communication requirements;
- phase 50: 292 call requirements;
- phase 51: 196 encryption requirements;
- phase 52: 542 safety, AI, fraud, report, and control requirements;
- phase 53: 286 interface and data-control requirements;
- phase 54: 411 quality, release, and scenario requirements.

## Next Production Package

Phase 46 must first select exact independently testable atoms and record schema,
indexes, duplicate-dialog identity, participant role semantics, server policy
matrices, block behavior, request expiry, migration/rollback compatibility,
query budgets, EN/LT/RU keys, and browser acceptance criteria. It must not add
message delivery until canonical dialog identity and membership authorization
are proven.

## Infrastructure Gates

- realtime delivery requires an approved transport and reconnect contract;
- file scanning requires a configured scanner and observable quarantine state;
- calls require signaling plus TURN relay infrastructure;
- E2EE requires a reviewed multi-device protocol and key lifecycle;
- server AI cannot inspect E2EE content without explicit compatible disclosure;
- native background behavior is outside the current server-rendered web
  runtime until a separate client/runtime package exists.
