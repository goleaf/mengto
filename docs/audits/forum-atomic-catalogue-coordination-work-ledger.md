# Forum Atomic Catalogue Coordination Work Ledger

Date: 2026-08-30
Branch and baseline: `main` at `ae4ac32`, aligned with `origin/main`
Principal: Codex `/root`

## Preservation And Authority Boundary

- The baseline tree already contains unrelated unstaged changes to
  `docs/implementation-plan.md` and
  `tests/Fixtures/DatabaseSeedCoverage.php`, plus the untracked ledgers
  `docs/audits/place-canonical-facts-work-ledger.md`,
  `docs/audits/places-management-verification-work-ledger.md`, and
  `docs/audits/shared-directory-card-completion-work-ledger.md`. Those bytes
  are user-owned and must remain present but outside this package's commit.
- Specialists are read-only. They may inspect files and run non-mutating,
  bounded commands, but they do not edit repository files, stage, commit,
  push, mutate databases, or start shared-state services.
- The principal owns requirement selection, cross-stream dependency decisions,
  the exact requirement-ID manifest, every repository edit, verification,
  finding disposition, evidence promotion, isolated staging, commit, and push.
- Only one dependency-safe work package is active at a time. No requirement is
  promoted from keywords, file presence, inferred similarity, or aggregate
  counts. Every status is based on direct implementation and executed evidence.
- Specialist discovery runs in bounded waves because four total agent slots are
  available. Scope exclusivity, not simultaneous execution, defines team
  independence.

## Read-Only Specialist Assignments

| ID | Team | Exclusive scope | Required structured deliverable | Status |
| --- | --- | --- | --- | --- |
| FAC-S01 | Original forum source | Immutable source prompt, source checksum/preservation contract, original forum requirements, conflicts, and requirements attributable only to the original source | Files inspected; exact unresolved IDs; dependency edges; immutable-source risks; recommended smallest package; executable checks | complete; category 25 has no primary-source dependency |
| FAC-S02 | Taxonomy and forum extension | Forum extension catalogue, category/taxonomy phases, forum control requirements, generated taxonomy evidence, and incomplete phase audits | Files inspected; exact unresolved IDs by dependency order; current implementation gaps across every required layer; smallest forum-first package; generator implications | complete; exact category-25 closure selected |
| FAC-S03 | Pet-profile revision | Pet-profile source-stream master plan, progress, canonical pet-profile documentation, and requirement evidence | Exact unresolved IDs; dependencies on forum/taxonomy; current status and direct evidence; next safe package after forum | queued |
| FAC-S04 | Social-relationships revision | Social-relationships master plan, progress, decisions/audits, safety and discovery requirements, and evidence | Exact unresolved IDs; dependency graph; implementation/evidence gaps; next safe package | queued |
| FAC-S05 | Content-feed revision | Content-feed master plan, progress, decisions/audits, lifecycle/media/audience/moderation/distribution requirements, and evidence | Exact unresolved IDs; dependencies; implementation/evidence gaps; next safe package | queued |
| FAC-S06 | Communication revision | Communication master plan, progress, architecture decisions/audit, canonical delivery requirements, and evidence | Exact unresolved IDs; dependencies; implementation/evidence gaps; next safe package | queued |
| FAC-S07 | Community revision | Community master plan, progress, architecture decisions/audit, membership/governance/knowledge/moderation requirements, and evidence | Exact unresolved IDs; dependencies; implementation/evidence gaps; next safe package | queued |
| FAC-S08 | Medical-record revision | Medical-record master plan, progress, architecture decisions/audit, clinical/consent/interoperability/retention requirements, and evidence | Exact unresolved IDs; dependencies; implementation/evidence gaps; next safe package | queued |
| FAC-S09 | Portal-architecture revision | Portal architecture source-stream plan and requirements, authentication/authorization dependencies, and current implementation/evidence | Exact unresolved IDs; dependency graph; portal blockers for earlier streams; next safe package | queued |
| FAC-S10 | Event-lifecycle revision | Portal events completion master plan, canonical event documents, lifecycle requirements, and current evidence | Exact unresolved IDs; dependencies; implementation/evidence gaps; next safe package | queued |
| FAC-S11 | Requirement evidence | Evidence overlay, generated atomic catalogue and matrix, phase index, completion plan, current progress, generator sources/scripts, and allowed status transitions | Exact unresolved catalogue counts and IDs relevant to the first package; generator ownership map; evidence sufficiency rules; prohibited direct edits; required checks | complete; source/output and promotion rules reconciled |
| FAC-S12 | Testing | Current tests, architecture gates, database isolation, factory/seed coverage, browser harness, and verification commands relevant to the candidate first package | Red-first test manifest; existing coverage and blind spots; exact focused/full/generator/browser commands; isolation risks | in progress |
| FAC-S13 | Final review | Frozen attributable diff only, after implementation; no discovery or implementation participation | Requirement-by-requirement compliance verdict; severity-ranked reproducible findings; security/data/performance/i18n/a11y/test-quality review; release recommendation | blocked until diff freeze |

## Principal Package Record

The first package is the evidence/verification closure for exactly
`forum.category.0237` through `forum.category.0294` inclusive. Its current
classification is `implemented but not fully verified`: implementation is
`implemented`, verification is `tested`, and final result is `in-progress`.
`animal.taxonomy.0021`, `forum.moderation.0012`, every primary-source
`forum.feature.*` atom, and category 26 are excluded. The exact manifest,
dependencies, affected paths, acceptance criteria, rollback, and verification
are recorded under FAC-C25 in `docs/implementation-plan.md`.

## Finding Dispositions

- Accepted: no new category-25 production behavior or test is justified; the
  existing focused contract already covers source order, persistence, locale
  trust, custom-category preservation, HTTP validation, rendering, typography,
  and the isolated browser entrypoint.
- Accepted: all non-external final gates must pass before promotion. Verification
  runs against a clean committed `main` baseline because unrelated shared-tree
  work is actively changing and must not enter test discovery or Pint.
- Accepted: missing Codex history entry `1785397895` blocks source
  reconstruction, while missing PCOV/Xdebug blocks coverage. Both checks must
  be executed and reported without being converted to passes; deterministic
  checksum/generator checks remain separate.
- Deferred outside the exact manifest: child-label translation completion,
  shared hardcoded filters/sorts, generic category visibility, administrator
  catalogue bounds, global taxonomy, case-report moderation, and all original
  forum-source atoms.

## Verification Evidence

No completion or requirement-promotion evidence has been recorded. Commands
and observed results will be added only after execution against the
attributable package.
