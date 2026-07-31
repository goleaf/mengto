# PawCircle Documentation Index

This index is the source-of-truth map for first-party documentation.

## Canonical Documents

| Document | Purpose | Owner | Status |
| --- | --- | --- | --- |
| `AGENTS.md` | Repository execution contract and quality gates | Engineering | Canonical |
| `PRODUCT.md` | Product identity, audience, and experience principles | Product | Canonical |
| `DESIGN.md` | Visual language and interaction principles | Design | Canonical |
| `docs/requirements.md` | Requirement taxonomy and traceability rules | Product + engineering | Canonical |
| `docs/product-requirements.md` | Active functional requirements | Product | Canonical |
| `docs/system-requirements.md` | Active technical behaviour requirements | Engineering | Canonical |
| `docs/non-functional-requirements.md` | Security, quality, accessibility, and operations requirements | Engineering | Canonical |
| `docs/architecture.md` | Current architecture and boundaries | Architecture | Canonical |
| `docs/domain-model.md` | Domain modules, roles, and state transitions | Product + engineering | Canonical |
| `docs/data-model.md` | Persistence model and integrity rules | Data | Canonical |
| `docs/security.md` | Threats and implemented controls | Security | Canonical |
| `docs/authorization.md` | Role, ownership, and temporary-access decisions | Security | Canonical |
| `docs/frontend.md` | Blade, Livewire, JavaScript, and navigation boundaries | Frontend | Canonical |
| `docs/livewire.md` | Livewire 4 rules and applicability matrix | Frontend | Canonical |
| `docs/guides.md` | Collaborative guide workflow, review, editing, and recovery | Product + engineering | Canonical |
| `docs/community-review.md` | Low-risk review panels and contextual-note workflow | Product + engineering | Canonical |
| `docs/mentorship.md` | Peer mentorship, matching, privacy, lifecycle, reports, seeding, and recovery | Product + engineering | Canonical |
| `docs/groups.md` | Persistent group identity, membership, privacy, management, seeding, and recovery | Product + engineering | Canonical |
| `docs/polls.md` | Persistent group content, polls, private files, concurrency, seeding, and recovery | Product + engineering | Canonical |
| `docs/tailwind.md` | Tailwind 4 rules and applicability matrix | Frontend | Canonical |
| `docs/accessibility.md` | Accessibility acceptance criteria | Design + QA | Canonical |
| `docs/localization.md` | Locale architecture and translation workflow | Product + frontend | Canonical |
| `docs/testing.md` | Automated and browser verification | QA | Canonical |
| `docs/seeding.md` | Factory, fixture, and seeder contracts | Data + QA | Canonical |
| `docs/seeding-coverage.md` | Generated per-model factory/seeder matrix | Data + QA | Canonical generated evidence |
| `docs/performance.md` | Measured budgets and query rules | Engineering | Canonical |
| `docs/caching.md` | Cache ownership and invalidation | Engineering | Canonical |
| `docs/integrations.md` | External-provider boundaries | Engineering | Canonical |
| `docs/deployment.md` | Deployment and rollback | Operations | Canonical |
| `docs/operations.md` | Runtime checks and incident response | Operations | Canonical |
| `docs/current-state-audit.md` | Modernization baseline and resolved findings | Engineering | Living evidence |
| `docs/implementation-plan.md` | Dependency-aware modernization plan | Engineering | Living plan |
| `docs/requirements/compliance-matrix.md` | Requirements-to-code-and-test mapping | QA | Living evidence |
| `docs/code-review.md` | Final structured review findings | Engineering | Living evidence |
| `docs/known-limitations.md` | External and environmental blockers only | Product + engineering | Living evidence |
| `CHANGELOG.md` | User-visible and operational history | Release | Canonical history |

## Forum Modernization Sources

The complete forum and global animal-taxonomy specification is an additive
domain contract under the canonical requirements above:

| Document | Purpose | Status |
| --- | --- | --- |
| `docs/requirements/forum-source-prompt.md` | Immutable recovered source specification and checksum | Canonical immutable input |
| `docs/requirements/forum-master-requirements.md` | Generated domain and state summary | Canonical generated |
| `docs/requirements/forum-requirements.json` | All 7,284 atomic requirement records | Canonical machine-readable |
| `docs/traceability/forum-requirements-matrix.md` | Requirement-level implementation and verification evidence | Living generated evidence |
| `docs/plans/forum-master-plan.md` | Dependency-aware implementation contract | Living canonical plan |
| `docs/plans/forum-phase-requirement-index.md` | One primary phase for every atomic requirement | Canonical generated |
| `docs/plans/forum-current-progress.md` | Current phase, checks, and preservation ledger | Living evidence |
| `docs/audits/forum-existing-system-audit.md` | Forum baseline and discovered architecture | Living evidence |
| `docs/audits/forum-gap-analysis.md` | Gaps, risks, and migration constraints | Living evidence |
| `docs/audits/forum-final-completeness-audit.md` | Gate status and final totals | Living evidence |
| `docs/decisions/forum-architecture-decisions.md` | Forum and taxonomy ADR summary | Canonical decisions |
| `docs/decisions/forum-assumptions.md` | Repository-derived assumptions | Living decisions |
| `docs/decisions/forum-conflicts.md` | Explicit conflict resolution | Living decisions |

Future forum work must read these documents after the global canonical
requirements and before changing forum production code. Statuses are
conservative: a generated file or checkbox is not proof of implementation.

## Feature Specifications

`docs/superpowers/specs` contains detailed product contracts for the pet
directory, pet profile, profiles, feed, connections, friendships, groups,
events, messaging, and places. They remain active only where they do not
conflict with the canonical requirements above.

`docs/superpowers/plans` records historical delivery plans. Completed checkboxes
prove only the earlier prototype scope; they do not prove production
authentication, persistence, localization, or current verification.

`docs/forum-scope.md` is the focused forum product boundary and supplements
`docs/product-requirements.md`.

`docs/requirements/laravel-engineering-standard.md` preserves the stable
`LAR-01` through `LAR-31` engineering identifiers. It is subordinate to
`AGENTS.md` when a rule is updated.

## Document Inventory

| Path or group | Apparent purpose | Current? | Duplication/conflict | Action |
| --- | --- | --- | --- | --- |
| `README.md` | Setup and contributor entry point | Current | None | Rewritten and maintained |
| `PRODUCT.md` | Product identity and product boundaries | Current | None | Principles preserved; boundaries normalized |
| `DESIGN.md` | Design system and responsive rules | Current | SCSS/Tailwind ownership is explicit | Preserved and aligned with Tailwind tokens |
| `CLAUDE.md` | Agent compatibility pointer | Current | No duplicated rule body | Reduced to durable pointer |
| `docs/architecture.md` | Runtime architecture and boundaries | Current | None | Rewritten |
| `docs/deployment.md` | Deployment and rollback checklist | Current | Environment verification remains operational | Rewritten |
| `docs/implementation-plan.md` | Dependency-aware modernization execution | Current living evidence | None | Replaced and updated per pass |
| `docs/requirements/compliance-matrix.md` | All 163 active requirements | Current generated evidence | None | Expanded and regenerated |
| `docs/requirements/laravel-engineering-standard.md` | Stable engineering rules | Current subordinate standard | Deliberate overlap with `AGENTS.md` | Preserve stable IDs |
| `docs/forum-scope.md` | Forum-specific scope | Current | None | Preserved |
| `docs/superpowers/specs/*.md` | Ten feature designs | Historical product source | Prototype assumptions are subordinate | Preserved with canonical override |
| `docs/superpowers/plans/*.md` | Ten historical implementation plans | Historical | Earlier no-test/no-Git rules are superseded | Preserved as non-canonical history |
| `CHANGELOG.md` | Release history | Current | None | Rewritten with preserved history and release evidence |

Third-party or generated instructions under `vendor`, `node_modules`,
`.agents/skills`, `.agents/vendor`, and `.claude/skills` are excluded from the
first-party documentation inventory.
