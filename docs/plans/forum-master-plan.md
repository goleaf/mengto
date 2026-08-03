# Forum Master Implementation Plan

## Contract

This plan implements the immutable source in
`docs/requirements/forum-source-prompt.md`. The generated
`docs/plans/forum-phase-requirement-index.md` assigns every one of the 38,377
atomic requirement IDs to a primary phase; no identifier is represented by a
generic “implement everything” checkbox.

The current verified/open totals, dependency-ordered remaining waves, and
final completion gates are maintained in
`docs/plans/forum-completion-plan.md`.

Before every phase:

1. read the source prompt, master requirements, matrix, progress, and
   applicable decisions;
2. inspect status and all uncommitted changes;
3. select the exact IDs from the generated phase index;
4. update affected files, schema/backfill/rollback, compatibility,
   authorization, validation, translation, interface, accessibility, cache,
   security, privacy, abuse, test, documentation, acceptance, verification,
   and evidence notes;
5. begin implementation only after the selected IDs are accounted for.

After every phase, update statuses and evidence, run targeted checks, record
newly discovered requirements, perform a completeness audit, and prepare the
next phase.

## Phase 0: Preserve And Atomize

**IDs:** all Phase 0 rows in the generated index.

**Result:** immutable recovered source, checksum, atomic JSON catalogue,
traceability matrix, and phase index. No schema impact. Verification:

```bash
php scripts/preserve-forum-source-prompt.php --check
php scripts/generate-forum-requirements.php --check
```

Rollback is file removal before publication only; the preserved source is
immutable after adoption.

## Phase 1: Repository Audit

**IDs:** all Phase 1 rows.

**Files:** audit, gap, progress, existing code/schema/test inventory.

**Acceptance:** current modules, roles, permissions, forum/pet models,
translation, search/cache, moderation, seeds/factories/tests, deployment, and
baseline are factual. No production data changes. Evidence is the audit plus
executed baseline commands.

## Phase 2: Domain Design

**IDs:** all Phase 2 rows.

**Result:** decisions for category identity, structured topic types, scoped
reputation, trust, confirmations, reports/cases, taxonomy provenance,
activation, adjacent-domain reuse, search, and Livewire boundaries.

**Risks:** table explosion and god services. Mitigation: create only concepts
needed by executable workflows and keep definitions data-driven.

## Phase 3: Additive Schema Foundation

**IDs:** all Phase 3 rows.

**Create/modify:** new migrations, enums/value objects, models, policies,
factories, schema tests, `forum_topics` additive foreign keys and versioned
structured data.

**Migration:** expand only, nullable links, no legacy column removal. Backfill
in bounded batches. Rollback drops only newly added empty relations/tables and
never existing forum data. Add foreign/unique/composite indexes based on
actual query paths. Validate direct access and race-sensitive uniqueness.

The topic-type schema runtime slice uses the existing additive columns: a
typed catalogue, bounded invalidating registry, dynamic HTTP validation, and
direct Action enforcement replace the remaining static/prototype assumptions.
Its exact 20-ID scope and gate status are maintained in
`docs/plans/forum-topic-type-schema-runtime-work-package.md`.

Phase 3 is complete at 82 verified IDs. The final 13-ID package adds no
speculative schema: it audits all migration sources and executes an isolated
118-file apply, complete rollback, exact reapply, and repeat-seed lifecycle.
Its scope and final evidence are maintained in
`docs/plans/forum-phase3-migration-verification-work-package.md`.

## Phase 4: Forum Category Taxonomy

**IDs:** all Phase 4 rows.

The first two verified Phase 4 slices cover exactly category 21's 64 atoms and
category 22 plus three translation/cache atoms, for 131 verified IDs total.
Runtime manifest validation fails closed for structural/source drift, warm
locale trees execute zero database queries, and unreviewed locale values can
no longer override reviewed fallback text. The remaining category roots and
technical taxonomy requirements stay open; exact evidence is maintained in
`docs/plans/forum-phase4-before-ownership-category-work-package.md` and
`docs/plans/forum-phase4-special-needs-category-work-package.md`.

**Result:** 44 stable root keys, every recovered required child, translations,
rules, aliases, redirects, related categories, deterministic ordering,
system/admin ownership, synchronizer, cache invalidation, and topic backfill.

**Compatibility:** old category strings remain accepted and redirect to the
new relation. Topic moves preserve IDs, answers, votes, engagements, reports,
attachments, history, and old URLs.

**Tests:** count/key/tree/translation/idempotency/preservation/redirect/cache
tests and bounded query assertions.

## Phase 5: Global Animal Taxonomy

**IDs:** all Phase 5 rows.

**Result:** sources, imports, taxa, names, identifiers, changes, domestic
classifications, registries, community groups, local overrides, selector
queries, admin import controls, and core seed.

**Data strategy:** local versioned Catalogue of Life Base Release snapshot,
Darwin Core mapping, checksum/license validation, dry-run impact report,
chunk-level transactions, lock, resume cursor, inactive staging, hierarchy
validation, atomic activation, cache invalidation, and rollback.

**Tests:** seed/import idempotency, checksum, source/version, resume/failure,
single activation, rollback, cycle/orphan, synonym/merge/split/override,
search, authorization, memory and query budgets.

## Phase 6: Reputation, Trust, Badges, And Confirmation

**IDs:** all Phase 6 rows.

**Result:** append-only scoped reputation and reversals, anti-abuse limits,
aggregates, audited trust changes, versioned badges, reviewer eligibility,
quorum/diversity/conflicts/expiry, panels, accepted-answer records, and
community notes.

**Security/abuse:** no self/duplicate/paid/reciprocal voting, capped influence,
no automatic professional authority, no public negative humiliation.

## Phase 7: Reports And Moderation

**IDs:** all Phase 7 rows.

**Result:** polymorphic reports and 88 reasons, duplicate grouping, triage,
private evidence, cases, immutable events, actions, restrictions, appeals,
recusal, transparency aggregates, block/mute safety, and credential review.

**Privacy:** least-privilege case access, reporter anonymity, private evidence
disk, redacted public outcomes. Permanent suspension requires authorized human
review.

## Phase 8: Structured Community Functionality

**IDs:** all Phase 8 rows.

**Work packages:** topic types/schemas; rich authoring/version history;
animal/pet context; guides; mentorship; structured lost/found and adoption
links; professional verification; service/marketplace trust; emergency mode;
groups; polls; journals; events; expert sessions; lifecycle; bookmarks and
collections.

Each work package receives its own requirement subset, action/policy/form,
schema/backfill/rollback note, locale keys, accessible UI, factories, tests,
and evidence before it is verified.

### Active Work Package: Existing-Domain Integration And Missing Foundations

**Current analysis:** `SearchCase`, `Sighting`, `SearchVolunteer`, expert
profiles/credentials, listings/orders/disputes/reviews, care journals,
knowledge articles/versions/corrections, pet profiles, forum topics, and
preview social/event state already exist. They remain authoritative. Missing
cross-domain capabilities are represented additively and may not copy private
records into forum tables.

**Desired result:** topic-to-domain context uses typed relations or validated
structured references; professional verification reuses credentials; guides
reuse knowledge versions; lost/found reuses search cases; marketplace trust
uses completed orders; journal functionality reuses care journals.

**Expected files:** additive migrations only where a durable missing invariant
requires storage; focused models/actions/policies/forms; EN/LT/RU catalogues;
factories and tests; this plan, progress, architecture decisions, and
traceability evidence.

**Data and rollback:** no legacy table or column is removed. New nullable
relations are backfilled only from deterministic identifiers. Ambiguous rows
remain unchanged and enter review evidence. Rollback removes only new,
unreferenced structures.

**Authorization/privacy:** every mutation rechecks policy server-side. Private
pet, credential, report, lost-case location, adoption applicant, and
transaction evidence is never copied into public forum payloads.

**Accessibility/interface:** class-based Livewire only, separate Blade,
keyboard-accessible controls, explicit loading/error/offline states, bounded
results, and no full-domain graph in public state.

**Acceptance and verification:** fresh migration, factory creation,
positive/negative policy tests, transition/idempotency tests, privacy tests,
targeted Larastan/Pint, localization gates, architecture tests, and production
asset build must pass before any included ID becomes verified.

## Phase 9: Search, Feed, Following, Notifications

**IDs:** all Phase 9 rows.

**Result:** privacy-first cross-domain search, filters, aliases/synonyms,
duplicates, transparent feeds, saved searches/history deletion, subscriptions,
granular preferences, quiet hours, and collections.

**Cache:** keys include visibility/user/locale/location context; invalidation
is deterministic. Tests prove private records do not leak via counts,
snippets, autocomplete, recommendations, or caches.

## Phase 10: Class-Based Livewire Interface

**IDs:** all Phase 10 rows.

**Components:** category browser, topic form, report workflow, confirmations,
reputation ledger, taxonomy selector, and administration. Each has a separate
Blade view, minimal typed state, locked immutable IDs, direct-action policy
checks, form object validation, loading/empty/error/offline states, stable
keys, responsive layout, and keyboard alternatives.

No Volt, large browser taxonomy, or duplicated business logic.

## Phase 11: Legacy Analysis And Backfill

**IDs:** all Phase 11 rows.

Produce category, type, ambiguity, slug, duplicate, unsupported-field,
lost/found, adoption, translation, and animal mapping reports. Low-confidence
records stay related to their original data and enter an admin review queue.
Every batch is restartable; no title-only sensitive classification.

## Phase 12: Production And Demo Seeds

**IDs:** all Phase 12 rows.

Split fixed definitions, forum categories, topic types, report reasons,
moderation actions, reputation dimensions, trust levels, badges, confirmation
states, core taxonomy, community groups, and local/demo graphs. Fixed seeds
are idempotent and non-destructive; demo records are environment-gated.

## Phase 13: Comprehensive Verification

**IDs:** all Phase 13 rows plus tests linked from every earlier phase.

Run targeted tests during each pass, then migrations, fresh seed, idempotency,
policy matrix, concurrency, privacy/security, architecture, localization,
query budgets, import, full suite, parallel suite when safe, coverage,
Larastan, Pint, Composer/NPM audits, production build, cache smoke, and browser
accessibility checks. Failures are fixed before verification status changes.

## Phase 14: Final Synchronization And Publication

**IDs:** all Phase 14 rows.

Reread every first-party Markdown file, update canonical architecture/data/
security/localization/testing/seeding/deployment/operations documents,
changelog, progress, matrix, and final audit. Inspect complete and staged
diffs, secrets, generated artifacts, and unrelated files. Commit coherent
slices with a temporary index and push only after observed successful gates.

## Phases 15-25: Pet Profile Revision

The additive pet-profile revision contributes 4,135 stable `pet.*`
requirements without renumbering the original 7,284 IDs. Control phases 15-16
cover preservation, audit, and domain design. Implementation phases 17-25
cover the canonical aggregate, creation and identity facts, ownership and
privacy, public/media/social behavior, integrations, lifecycle and memorial,
moderation, localization/accessibility, and scenario verification.

The complete requirement ranges, risks, compatibility strategy, acceptance
gates, and evidence contract are maintained in
`docs/plans/pet-profile-master-plan.md`. Pet work does not replace or defer the
remaining forum/taxonomy phases; it reuses their taxonomy, moderation,
adoption, lost/found, event, guide, search, cache, and translation boundaries.

## Phases 26-34: Social Relationships Revision

The additive social-relationships revision contributes 3,210 stable
`social.*` requirements without renumbering the prior 11,419 IDs. The control
phase preserves, audits, and designs the revision. Implementation phases 27-34
cover relationship/follow foundations; requests, safety, and moderation; pet
friendship and safe meetings; recommendations and search; messaging, groups,
events, privacy, and notifications; interface/accessibility/localization;
release quality; and ideal-scenario verification.

The exact phase boundaries, invariants, migration strategy, and evidence
contract are maintained in
`docs/plans/social-relationships-master-plan.md`. Social relationships point
to existing user, pet, expert, organization, group, event, moderation, and
message aggregates. They never replace those identities or grant their
administrative permissions.

## Phases 35-44: Content Feed And Distribution Revision

The additive content-feed revision contributes 4,011 stable `content.*`
requirements without renumbering the prior 14,629 IDs. Control phase 35
preserves, audits, and designs the revision. Implementation phases 36-44 cover
the publication/audience/media foundation; authoring and lifecycle; stories,
feeds, and social interactions; media processing; quality, search, analytics,
and notifications; safety and moderation; domain integrations and optional AI;
localization, accessibility, and resilient web operation; and complete release
scenario verification.

The exact boundaries, invariants, compatibility strategy, and acceptance gates
are maintained in `docs/plans/content-feed-master-plan.md`. Canonical content
links to existing forum, pet, social, group, event, lost/found, adoption,
marketplace, knowledge, care, moderation, and media records. It does not copy
their private or mutable business state into feed payloads.

## Phases 45-54: Communication Revision

The communication revision contributes 3,877 stable `communication.*`
requirements. Its exact message, media, group communication, call, encryption,
safety, interface, and release boundaries are maintained in
`docs/plans/communication-master-plan.md`.

## Phases 55-63: Community Revision

The community revision contributes 3,576 stable `community.*` requirements.
Its exact group identity, membership, governance, forum, knowledge, event,
volunteer, finance, moderation, discovery, lifecycle, and release boundaries
are maintained in `docs/plans/community-master-plan.md`.

## Phases 64-73: Medical Record Revision

The medical revision contributes 3,867 stable `medical.*` requirements. The
first package links the existing medical aggregate to canonical pet identity,
makes ownership transfer contract access, introduces explicit medical
knowledge states, and preserves legacy rows. The full clinical, provider,
consent, emergency, security, interoperability, interface, and scenario plan
is maintained in `docs/plans/medical-record-master-plan.md`.
