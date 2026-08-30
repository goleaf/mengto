# Places Management And Verification Work Ledger

Date: 2026-08-30
Branch: `main`
Baseline: `5008937`
Plan owner: `PCLM-01` through `PCLM-10` in
`docs/implementation-plan.md`

## Preservation Contract

- The shared tree was materially dirty before this package. Every pre-existing
  staged, unstaged, and untracked change is user-owned and must be preserved.
- Specialists are read-only. The principal owns every cross-module decision,
  implementation edit, test edit, documentation edit, stage, commit, and push.
- Specialist reports use current repository evidence, exact paths/symbols,
  explicit risks, recommended invariants, and executable test cases.
- Database mutation verification uses the repository's isolated test and fresh
  database tooling only. No configured development or production data is reset.
- A final attributable diff is frozen before an independent specialist review.
  Valid findings are reproduced, dispositioned, fixed, and reverified before
  publication through a temporary Git index.

## Specialist Assignments

| Scope | Exclusive responsibility | Structured deliverable | Edit authority | Status |
| --- | --- | --- | --- | --- |
| Claim state machine and expiration | Exact eight-state graph, transition actors, terminal/reopen behavior, renewal/supersession, timestamp-derived expiry and bounded expiry operation | Files inspected; transition table; invariants; transaction/locking design; negative/replay/expiry tests; risks | read-only | pending |
| Organization roles and authorization | Existing organization role/capability/restriction semantics plus claimant, manager, owner, reviewer, moderator, administrator, member, blocked, inactive, outsider matrix | Files inspected; capability matrix; query/policy boundaries; recusal/conflict rules; removal/transfer/history tests; risks | read-only | pending |
| Evidence privacy, notifications, and concurrency | Private storage/response patterns, safe metadata and audit boundary, abuse reports, after-commit/idempotent notifications, conflict uniqueness, row/optimistic locks and concurrent approval | Files inspected; privacy/threat model; event/delivery design; schema constraints; race harness; file/report/notification tests; risks | read-only | pending |
| Independent final security review | Frozen attributable implementation/test/documentation diff only; no participation in implementation | Severity-ranked findings with exact paths/lines, reproduction, requirement mapping, required fixes, and release recommendation | read-only and independent | blocked until diff freeze |

## Principal Decision Record

- The user request and canonical `PLA-P07` requirements are the approved design;
  no routine clarification is required.
- A claim is evidence-bearing review state, not authority. Current authority is
  a separate explicit scoped relation created only by an approved transition.
- Historical attribution is immutable. Revocation, expiry, removal, scope
  replacement, and transfer end current authority without rewriting authorship.
- Evidence content is never copied into audit metadata, notification payloads,
  browser state, logs, official-response presentation, or unrestricted URLs.

## Review Dispositions

The principal appends specialist decisions and final review dispositions here
before publication. No reviewer edits tracked files.
