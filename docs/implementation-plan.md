# Production Modernization Plan

Plan date: 2026-07-30

This living plan records work that was actually performed. A pass is
`verified` only when its listed check completed successfully. Requirement-level
status remains authoritative in
`docs/requirements/compliance-matrix.md`.

The reconciled current backlog is maintained in
`docs/plans/current-unfinished-work.md`. Completed deliveries below are release
evidence, not active backlog items.

## Active Delivery: Configurable Email Verification

Status: `approved; implementation planning in progress` on 2026-08-30.

This delivery implements the approved
`docs/superpowers/specs/2026-08-30-configurable-email-verification.md`
contract. The deployed environment will use
`EMAIL_VERIFICATION_ENABLED=false`; new registrations will be activated
without a verification email, while `true` preserves the current fail-closed
verification flow. Authentication, active-account checks, policies, scoped
private access, password confirmation, and all non-email verification domains
remain mandatory in both modes.

The task starts on `main` at `38713ac` in a materially dirty shared tree whose
Places implementation and pending migrations are unrelated user-owned work.
Those files remain byte-preserved and outside this delivery's commit. The
approved email-verification slice uses a temporary `GIT_INDEX_FILE` for any
task commit. The current deployed SQLite baseline contains one active account
with a null `email_verified_at`; mutation requires a timestamped backup,
observed pre/post counts, and an integrity check.

| ID | Dependency | Owner | Affected paths | Acceptance criteria | Verification | Status | Rollback |
| --- | --- | --- | --- | --- | --- | --- | --- |
| EVR-01 | Approved configurable-email-verification specification | Principal | `.env.example`, `config/platform.php`, `phpunit.xml`, email-verification mode service | One boolean environment value is read only through configuration; secure enabled mode is the committed and automated-test default; invalid/non-false values fail closed | Focused configuration feature tests and config-cache smoke | planned | Set the deployed value to `true` and revert the configuration slice |
| EVR-02 | EVR-01 | Principal | `RequirePortalAccess`, configuration-aware `verified` middleware, `bootstrap/app.php` | Guests and inactive users remain denied in both modes; enabled mode blocks active unverified users; disabled mode permits only otherwise authenticated/active/authorized access through central and explicit verified middleware | Red/green portal-boundary tests, route middleware inspection, auth regressions | planned | Restore Laravel's default alias and unconditional central verification check |
| EVR-03 | EVR-01..02 | Principal | `RegisterUser`, registration and verification Livewire components, auth tests | Enabled mode creates a pending account, sends one verification notification, and redirects to notice; disabled mode atomically stamps `email_verified_at`, sends no verification notification, redirects home, and never renders/resends the verification notice | Red/green registration, notification, direct-page, and Livewire-action tests | planned | Re-enable verification; already activated accounts intentionally retain their timestamp |
| EVR-04 | EVR-01 | Principal | Bounded activation Action, Artisan command, audit records, auth tests | Dry-run is non-mutating; writes are refused while enabled; disabled execution activates only active pending accounts in locked bounded transactions, writes one non-sensitive audit per account, and is idempotent | Action/command tests covering active, blocked, suspended, verified, repeat, audit, and enabled-mode refusal | planned | Re-enable verification; use backup and audit evidence for deliberate account-level reversal only |
| EVR-05 | EVR-01..04 | Principal | `DatabaseSeeder`, canonical requirements/security/authorization/testing/seeding/deployment docs, compliance generator/output, changelog | Disabled-mode demo seeding does not recreate an active pending email account; repeated seed remains stable; all canonical statements describe the conditional assurance boundary and generated output is byte-current | Focused seeder test, generator diff/check, documentation and secret review | planned | Revert documentation and conditional demo timestamp with the code slice |
| EVR-06 | EVR-01..05 | Principal/operator | Deployed `.env`, `database/database.sqlite`, runtime caches | Set the deployed value to `false`; back up the exact SQLite database; inspect and apply pending migrations; run the environment-safe root seeder; dry-run and execute pending-account activation; prove zero active pending accounts and successful SQLite integrity | `scripts/artisan-runtime migrate:status`, `migrate --force`, `db:seed --force`, activation dry-run/write/repeat, count query, `PRAGMA integrity_check` through a supported SQLite client | planned | Restore the timestamped database backup only through deliberate operator recovery; setting `true` affects future registrations |
| EVR-07 | EVR-02..06 | Principal | Complete attributable diff and affected runtime | Targeted tests, Pint, Larastan, complete sequential Pest, isolated fresh/repeat seed, dependency audits, Vite build, config/route/view cache smokes, HTTP auth flow, diff, and secret checks are observed and truthfully recorded before task commit | Exact repository quality gates and post-mutation runtime evidence | planned | Revert the coherent code/docs commit; preserve database activation unless an explicit audited recovery is chosen |

Implementation order is `EVR-01` through `EVR-07`. Every behavior change starts
with an observed failing test. Operational migration and seeding happen only
after a database backup and after the pending migration list is captured; the
three currently pending Places migrations are outside this implementation
slice even though the user-requested runtime migration command may apply them.

## Active Delivery: Shared Place Submission And Publication

Status: `implementation and focused verification complete; independent review
and final repository gates in progress` on 2026-08-30.

The 2026-08-30 resumed execution revalidates the existing attributable slice
with fresh read-only workflow, duplicate-detection, moderation, security, and
testing specialists before the independent frozen-diff review. The principal
continues to own every edit, finding disposition, final gate, and publication
decision.

This delivery implements the complete `PLA-P06` workflow on top of the
preserved place/location authority foundation and the applicable `PLA-P02`,
`PLA-P03`, and `PLA-P05` contracts. The exclusive specialist scopes,
deliverables, and independent-review boundary are recorded in
`docs/audits/places-submission-publication-work-ledger.md`. Specialists remain
read-only; the principal owns every cross-module decision and tracked edit.

The task began on `main` at
`153ae45c2bc6864ec6061dc407d82be68a437c26`, aligned with `origin/main`, in a
materially dirty shared tree. Every pre-existing staged, unstaged, and
untracked path is unrelated user-owned work unless this ledger proves an exact
attributable hunk. Publication will use a temporary `GIT_INDEX_FILE`; no
existing change may be reset, discarded, or included accidentally.

| ID | Dependency | Owner | Affected paths | Acceptance criteria | Verification | Status | Rollback |
| --- | --- | --- | --- | --- | --- | --- | --- |
| PLA-SUB-01 | PLA-P02/P03/P05/P06 and preserved authority baseline | Principal plus read-only workflow, duplicate, moderation, and security specialists | Places plans/audit ledger, existing schema/models/actions/policies/routes/UI | Canonical lifecycle, identities, privacy boundaries, duplicate signals, role capabilities, idempotency, notification timing, and rollback are mapped before production edits | Repository inventory, specialist reports, attributable-path review | complete | Revert planning-only additions |
| PLA-SUB-02 | PLA-SUB-01 | Principal | `tests/Feature/Places/**`, focused unit tests | Red tests cover validated active-member submission, two-account isolation, deterministic duplicate candidates, repeated/concurrent operations, moderation transitions, merge rollback, redirects, authorization, notifications, provenance, and audit history | Focused Pest failures observed before implementation | complete; red/green contracts retained | Revert only the new red contracts |
| PLA-SUB-03 | PLA-SUB-02 | Principal | Additive migration, Places enums/models/factories and existing `Place`/`User` relations | Submission, fact provenance, candidate, event, merge redirect, and notification persistence is indexed, privacy-safe, reversible, factory-backed, and enum-cast | Fresh migration, rollback cycle, schema/factory contracts | complete; 140-file migration cycle and 227-table fresh seed pass | Roll back the additive migration before production writes; forward-fix after writes |
| PLA-SUB-04 | PLA-SUB-03 | Principal | Places data objects, normalizers, duplicate service, submission/moderation/merge Actions, policies | Server-authoritative input creates pending review state; candidates are deterministic suggestions; transitions, merges, restore/reopen, idempotency, abuse controls, after-success notifications, provenance, and audit are transactional and policy-scoped | Action, policy, concurrency, privacy, rollback, and notification tests | complete; isolated two-process race passes | Revert implementation with its schema only before production writes; otherwise forward-fix |
| PLA-SUB-05 | PLA-SUB-04 | Principal | Class-based Places Livewire components/forms/views, routes, EN/LT/RU catalogues | Members can submit and inspect safe status; authorized reviewers can act; loading, validation, pending, duplicate, approved, rejected, empty, and offline states are localized, keyboard-usable, and non-leaking | Livewire direct-action tests, locale parity, view/cache checks, browser journeys | complete; dedicated desktop/mobile Places browser journey passes | Revert route/component/presentation slice while preserving submitted records |
| PLA-SUB-06 | PLA-SUB-03..05 | Principal plus testing specialist | Places factories, deterministic demo seeder, root seed integration, database tests | Every new model has a bounded factory; deterministic pending/duplicate/needs-info/published/rejected/merged scenarios are repeat-safe and environment-gated | Factory/seeder tests, isolated fresh/repeat seed, count and relation checks | complete; twenty submissions and ten merge/restore scenarios seed repeat-safely | Remove demo-only synchronization; never delete user submissions |
| PLA-SUB-07 | PLA-SUB-02..06 | Independent final reviewer and principal | Frozen attributable diff | Independent review reproduces every material finding; valid findings are fixed and affected gates rerun | Review report, disposition ledger, focused reruns | in progress | Revert unsafe finding-specific change |
| PLA-SUB-08 | PLA-SUB-07 | Principal | PLA-P06 evidence, current progress, compliance/data/security/testing/seeding/deployment docs, changelog | Documentation matches observed behavior; focused Places, migration/seed, full Pest, Pint, Larastan, dependency, Vite, cache, browser, diff, and secret gates pass before one attributable commit | Exact final command evidence and temporary-index diff | in progress; final full-suite rerun and publication disposition remain | Revert the coherent task commit normally; never rewrite history |

Implementation order is `PLA-SUB-01` through `PLA-SUB-08`. Tests precede
production behavior. New submissions remain review records until an authorized
publication transition; duplicate scoring never merges by itself. Protected
identifiers and pending facts are scoped before presentation, and audit or
provenance rows are retained through merge and restore operations.

## Active Delivery: Forum Phase 4 Animal-Science Category

Status: `implemented; final verification pending` on 2026-08-30.

The unresolved placeholder in the initiating request is resolved from the
canonical `forum-current-progress` next-pass instruction: this delivery owns
the next dependency-safe Phase 4 source section, category 25, and no wider
phase. The exact 58-ID scope is `forum.category.0237` through
`forum.category.0294`; the two same-section atoms assigned to Phases 5 and 7
remain open. The acceptance, dependency, verification, rollback, and exact-ID
contracts are recorded in
`docs/plans/forum-phase4-animal-science-category-work-package.md`; specialist
coordination is recorded in
`docs/audits/forum-phase4-animal-science-work-ledger.md`.

| ID | Dependency | Owner | Affected paths | Acceptance criteria | Verification | Status | Rollback |
| --- | --- | --- | --- | --- | --- | --- | --- |
| F4-AS-01 | Canonical source and generated catalogue | Principal | Package plan and work ledger | All 58 selected records are listed and reconciled; adjacent Phase 5/7 records are excluded | Exact `jq` inventory and source/generator checks | complete | Revert planning files only |
| F4-AS-02 | F4-AS-01 | Principal | Category manifest/synchronizer/model/factory/seeder paths if required | Existing implementation is retained when correct; every real schema, persistence, or localization defect is test-first repaired | Focused red/green feature contract | complete | Revert finding-specific implementation with its test |
| F4-AS-03 | F4-AS-01..02 | Principal | EN/LT/RU, server-prepared UI, accessibility/responsive paths if required | Category 25 renders the exact localized root/purpose/ordered hierarchy without exposing later-phase completion claims | Focused HTTP and browser checks | complete | Revert presentation change and translations together |
| F4-AS-04 | F4-AS-02..03 | Principal | Requirement evidence and generated progress/traceability documents | Exactly the 58 implemented/tested IDs remain in progress until all required release gates pass; generated artifacts remain deterministic | Source preservation, forum generator, exact overlay delta | pending final promotion; source-history and failed repository gates retained | Revert evidence entry and regenerate |
| F4-AS-05 | F4-AS-04 | Independent reviewer and principal | Frozen attributable diff | Material findings are reproduced, dispositioned, fixed, and retested; full final gates either pass or retain exact blockers | Review report, targeted/full gates, temporary-index diff, `git diff --check` | pending | Revert the coherent package commit normally |

Implementation order is F4-AS-01 through F4-AS-05. Production code cannot
precede a failing behavior contract. The package is committed through a
temporary index so the pre-existing staged database/repository/auth work stays
byte-present and outside this delivery.

## Active Delivery: Complete Database Domain Audit And Implementation

Status: `implementation and independent rereview complete; publication blocked
by unavailable immutable forum-history evidence` on 2026-08-30.

This section is the canonical execution record for the complete migration,
model, relationship, factory, seeder, and database-integrity pass. The
specialist work ledger is
`docs/audits/database-domain-audit-work-ledger.md`. Discovery is read-only;
the principal agent owns every tracked edit and begins test-driven
implementation immediately after this plan is saved.

### Protected baseline and current inventory

- Branch and initial task HEAD: `main` at
  `fdaf7292a152ae61b85e17cf1ce69449d6d4292f`, matching `origin/main`.
- The task began in a dirty shared tree. All pre-existing staged, unstaged, and
  untracked work is preserved. The attributable publication slice will use a
  temporary `GIT_INDEX_FILE`.
- Runtime: PHP 8.5.8, Laravel 13.23.0, Pest 4, SQLite for isolated automated
  verification, and the repository's configured Pint and Larastan gates.
- Audited baseline: 139 migrations create or alter 218 named tables plus
  Laravel's migration ledger at runtime, with 3,478 columns and 514 foreign
  keys. The generated database-domain audit records every index and unique
  constraint. All 204 concrete application models have factories; 44 Seeder
  classes plus the representative manifest and demo guard trait provide root
  orchestration, focused seeders, and bounded representative top-up.
- A safe temporary-SQLite fresh migrate/seed completed and remained stable on
  a second seed, but produced only five users. Across the 203 models, 163 had
  fewer than ten rows and 70 had none. The existing dynamic factory suite
  passed 1,791 tests and 5,313 assertions, proving individual factory
  persistence but not complete representative seeding.
- Confirmed discovery defects before implementation: six `belongsTo`
  declarations infer nonexistent columns; `forum_topic_moves` is the only
  application-owned table without a corresponding model/factory; additional
  schema-backed child and inverse relationship candidates require final
  usage review before addition.
- Final implementation inventory: all 204 concrete application models have a
  valid factory and at least ten rows after a clean root seed; the generated
  audit covers all 3,395 model-contract columns, 514 foreign keys, 941 declared
  relationships, 267 explicit factory helpers, and the complete model/pivot
  seed graph. The deterministic `user@example.com` account is one of exactly
  ten clean-seed users and remains unique after a repeat seed.
- Safety incident: before the isolated runner was hardened, one exploratory
  factory command wrote additive sample rows to the configured shared SQLite
  database. No rows were deleted or overwritten. All subsequent destructive
  or persistence verification used asserted operating-system temporary
  SQLite databases; the pre-existing shared data was left untouched.

### Delivery items

Every item records dependencies, ownership, affected paths, acceptance,
verification, status, and rollback. Discovery specialists do not edit tracked
files. The principal implements and dispositions every finding; DBA-09 is an
independent reviewer of the frozen attributable diff.

| ID | Dependency | Owner | Affected paths | Acceptance criteria | Verification | Status | Rollback |
| --- | --- | --- | --- | --- | --- | --- | --- |
| DBA-01 | None | Schema specialist | `database/migrations/**`, schema evidence | Every migration/table/column/key/index/pivot/morph edge is inventoried from a fresh isolated database | Migration inventory and schema introspection artifact | discovery complete | Remove evidence only |
| DBA-02 | DBA-01 | Model specialist | `app/Models/**` | Every concrete model is mapped to its table, key, traits, casts, factory, and declared relations; mismatches are enumerated | Reflection/model-to-schema audit | complete | Remove evidence only |
| DBA-03 | DBA-01..02 | Relationship specialist | `app/Models/**` | Every reliable schema-backed child relation and appropriate inverse/pivot/morph relation exists with explicit relation types and correct keys | Red/green schema relationship contracts and round-trip tests | complete | Revert relationship methods and paired tests |
| DBA-04 | DBA-01..02 | Factory specialist | `database/factories/**` | Every applicable model has a valid realistic factory; required, unique, enum, encrypted, JSON, date, money, and representative nullable values satisfy schema/domain rules | Dynamic factory persistence and field-coverage tests | complete | Revert factory/state changes together |
| DBA-05 | DBA-03..04 | Factory-graph specialist | Factories and relation helpers | Parent reuse avoids circular creation and exponential graphs; pivot and polymorphic metadata are valid | Factory graph audit plus bounded count assertions | complete | Revert graph helpers only |
| DBA-06 | DBA-01..05 | Seeder specialist | `database/seeders/**` | Root seeding is dependency-ordered, environment-safe, reasonably idempotent, creates `user@example.com`, and creates a coherent graph with at least ten rows for every concrete persistent application model without deleting existing data | Isolated fresh seed, repeat seed, counts, pivots, and target-account assertions | complete | Remove additive representative seeder and restore root orchestration |
| DBA-07 | DBA-01..06 | Constraint specialist | Models, factories, seeders, validation, enums | Database and application uniqueness, checks, state/date/money constraints, casts, and tenant/owner boundaries are reflected in generated data | Constraint matrix and focused negative/positive Pest coverage | complete | Revert finding-specific change |
| DBA-08 | DBA-03..07 | QA specialist | `tests/Feature/Database/**`, lifecycle scripts | Tests prove factories, required/representative fields, root seeding, counts, target user, relations, pivots, foreign keys, uniqueness, and repeatability on isolated SQLite | Targeted Pest suites and fresh lifecycle scripts | complete; 1,130 database feature tests / 4,047 assertions and both isolated lifecycle scripts passed | Revert tests/scripts with their implementation slice |
| DBA-09 | DBA-08 | Independent reviewer | Frozen attributable diff | Migration-model-factory-seeder-test chain is adversarially reviewed; every material finding is reproduced, dispositioned, fixed when valid, and retested | Review report and rerun affected gates | complete; three independent adversarial rereviews report release ready with no material attributable finding | Revert unsafe finding-specific repair |
| DBA-10 | DBA-09 | Principal | Documentation, complete task slice | Audit/schema evidence and seeding docs are current; targeted/full PHP gates, Pint, Larastan, Composer checks, npm audit/build, caches, isolated migrate/seed, generator parity, forum preservation checks, diff/secret review pass or an exact external blocker is recorded; coherent attributable commit is safely pushed | Definition-of-done gate list and Git evidence | implementation and evidence complete; publication blocked, so no commit or push | Revert task commit normally; never rewrite history |

### Final observed verification and publication disposition

- Partitioned PHP verification: 2,442 tests and 105,112 assertions; 2,441
  tests passed. The sole failure is the immutable forum-source preservation
  contract because required historical entry `1785397895` is unavailable.
- The database feature partition passed 1,130 tests and 4,047 assertions. The
  complete unit partition passed 32 tests and 218 assertions. The audit
  regeneration subprocess that once received signal 11 passed on an isolated
  retry; the oversized combined PHP 8.5 process remains operationally
  unstable, so final verification is recorded from bounded sequential runs.
- Fresh lifecycle passed with 139 migrations, 219 runtime tables, ten users
  before and after repeat seeding, complete rollback to zero migrations, and
  successful reapplication of all 139 migrations.
- Pint, Larastan, Composer validation/audit/platform checks, official-registry
  npm audit, production Vite build, isolated config/route/view cache smoke,
  all three generated-evidence parity checks, and the 38,377-item forum
  requirements generator passed.
- Publication is blocked by the required forum-source preservation failure.
  Repository policy permits no push until required gates pass; no task commit
  or push was made in the dirty shared tree.

### Implementation and verification order

1. Reconcile DBA-01 through DBA-07 findings into a durable schema and model
   audit, then observe failing Pest contracts for confirmed defects.
2. Correct relationship keys and inverses, add the missing topic-move model and
   factory, and repair only evidence-backed factory/state gaps.
3. Build an environment-gated representative seeder in dependency order. It
   tops up deficits without truncating data, uses real model instances rather
   than hardcoded IDs, connects pivots with their metadata, and makes
   `user@example.com` a deterministic, verified, fully connected account.
4. Prove every concrete model reaches at least ten rows on a clean seed,
   meaningful nullable fields have representative non-null coverage where
   valid, all foreign keys pass, the second seed does not regress counts, and
   key relationship round trips resolve.
5. Run targeted tests first, then fresh lifecycle checks, Pint, Larastan, the
   full Pest suite, Composer validation/audit/platform checks, npm audit/build,
   route/config/view-cache smoke checks, documentation generators, forum
   source/generator checks, independent review, and final attributable diff
   review. Update these statuses only from observed results.

## Active Delivery: Complete Repository Audit And Foundational Repair

Status: `implementation approved from repository evidence` on 2026-08-30.

This section is the canonical execution record for prompt 01. Discovery was
performed read-only by the seven specialist scopes in
`docs/audits/repository-audit-work-ledger.md`; production changes begin only
after this section was saved. Later numbered prompts under `docs/prompts/`
remain the owners of broad modernization and are not silently pulled into this
foundational pass.

### Protected Git And Instruction Baseline

- Initial branch and HEAD: `main` at
  `93a4595b136c3e0a8b7f4671215af91487d5f9e7`, tracking `origin/main` at the
  same commit.
- Initial tree: 89 staged paths and one unstaged path. The unrelated slice
  contains first-party documentation, Playwright capture YAML, screenshot
  deletions, and the unstaged `docs/validation-error-work-ledger.md` update.
  A concurrent workspace operation committed most of that slice as
  `fdaf7292a152ae61b85e17cf1ce69449d6d4292f` and advanced `origin/main` while
  this audit was running. The remaining Playwright YAML deletions remain
  unrelated. Both states are preserved and excluded from the audit commit
  through a temporary `GIT_INDEX_FILE`.
- Applicable instruction chain: root `AGENTS.md` only. No first-party nested
  `AGENTS.md` or `AGENTS.override.md` exists. `CLAUDE.md` is a supporting
  pointer, not an override.
- Authority order: `AGENTS.md`; canonical requirements; security/privacy/data
  integrity; accepted architecture/ADRs; this plan and subordinate domain
  plans; accurate tests/code; supporting evidence; historical plans/specs.

### Complete First-Party Markdown Classification

The initial audit classified all 235 first-party Markdown files. The path
patterns below preserve that initial snapshot. Concurrent first-party work
raised the live total to 241; the generated, per-path authority table in
`docs/audits/repository-inventory.md` is the exhaustive current inventory.
Generated/tooling/vendor trees
under `.agents`, `.claude`, `.cursor`, `vendor`, `node_modules`, and runtime
caches are excluded.

| Paths | Count | Authority |
| --- | ---: | --- |
| `AGENTS.md`, `CHANGELOG.md`, `DESIGN.md`, `PRODUCT.md`, `SECURITY.md` | 5 | Canonical |
| `CLAUDE.md`, `README.md` | 2 | Supporting entry points |
| Canonical cross-cutting documents named by `docs/index.md`, from `docs/accessibility.md` through `docs/topic-lifecycle.md` | 36 | Canonical |
| `docs/{api-integrations-work-ledger,code-review,comprehensive-php-test-suite-work-ledger,current-state-audit,design-system,known-limitations,seeding-work-ledger,ui-component-inventory,ui-migration-matrix,validation-error-work-ledger}.md` | 10 | Supporting/living evidence |
| `docs/events.md` | 1 | Historical; superseded by `docs/events/index.md` |
| `docs/seeding-coverage.md` | 1 | Generated evidence |
| `docs/components/shared-card-primitives.md` | 1 | Canonical component contract |
| `docs/audits/*.md`, excluding `pet-social-network-benchmark.md` | 20 | Supporting dated/living evidence |
| `docs/audits/pet-social-network-benchmark.md` | 1 | Historical research |
| Ten architecture/feature decision files under `docs/decisions/` | 10 | Canonical decisions |
| Eight `*-assumptions.md` and `*-conflicts.md` files under `docs/decisions/` | 8 | Supporting decision evidence |
| `docs/events/index.md` | 1 | Canonical event-system index |
| Every other `docs/events/*.md` | 29 | Supporting event specifications/evidence |
| Eleven named domain master/completion plans under `docs/plans/` | 11 | Canonical within scope; subordinate to this plan |
| Other `docs/plans/*.md`, excluding the two following rows | 58 | Supporting work-package evidence |
| `docs/plans/join-landing-page-plan.md` | 1 | Historical/superseded |
| `docs/plans/forum-phase-requirement-index.md` | 1 | Generated evidence |
| `docs/portal/*.md` | 8 | Supporting portal contracts |
| `docs/requirements/{forum-source-prompt,laravel-engineering-standard}.md` | 2 | Canonical; source prompt is immutable |
| `docs/requirements/{compliance-matrix,forum-master-requirements}.md` | 2 | Generated canonical evidence |
| `docs/superpowers/plans/*.md` | 10 | Historical prototype evidence |
| `docs/superpowers/specs/*.md` | 16 | Historical/subordinate product sources |
| `docs/traceability/forum-requirements-matrix.md` | 1 | Generated living evidence |

There is no competing global plan. Domain plans remain scoped. No `PLANS.md`
or ceremonial replacement is required.

### Repository And Runtime Inventory

| Surface | Current factual inventory |
| --- | --- |
| Routes | 180 runtime routes after `optimize:clear`, including one Boost development route; 179 audited application/framework routes; 167 first-party `App\\` actions; 173 named |
| HTTP/runtime entry points | `routes/web.php`, five console commands, no scheduled tasks, no first-party Jobs/Events/Listeners/Notifications |
| Application layers | 147 controllers; 9 middleware; 67 Form Requests; 226 Actions; 155 Services; 204 models; 47 policies; 1 API Resource; 1 service provider |
| Livewire/Blade | 86 Livewire PHP files: 37 components and 49 form objects; 36 Livewire views; 357 Blade views including 246 anonymous Blade components; no Volt/Flux/Filament |
| Persistence | 139 migrations create 218 named tables; isolated fresh migrate/seed reports 219 including Laravel's migration ledger; 514 declared constrained foreign keys sampled by integrity tests |
| Factory/seed | 204 model factories plus `ApplicationFactory`; 44 Seeder classes plus the representative manifest and demo guard trait; 267 explicit invariant-aware factory helpers |
| Tests | 120 feature files, 3 unit files, 128 PHP files including support/bootstrap, 1,025 Pest declarations, zero Pest browser files, standalone Node browser runners |
| Frontend | 9 resource JavaScript modules, 1 Tailwind CSS entry, 32 CSS/SCSS files, npm lock v3; PhotoSwipe is the only production npm dependency |
| Roles/capabilities | Active/blocked account status plus explicit administrator flag; pet-manager, forum-group, journal-collaborator, knowledge-collaborator, organization, event-team, and event-session role enums; policies are authoritative |
| Integrations/processes | No first-party outbound HTTP client, webhook, worker job, or scheduler; private/local and authenticated portal-file adapters; synchronous queues; operator-run deployment documentation |
| Cache | Public listing/search aggregates with TTL/invalidation plus taxonomy caches and atomic locks for taxonomy, place, event, and state mutations |
| Localization | Laravel language catalogues for `en`, `lt`, and `ru`, 45 files per locale; `LocaleFormatter` depends on Intl |

The module map is: bootstrap/access; identity; pet/taxonomy; encrypted social
compatibility state; normalized social/content; forum/knowledge/community;
organizations; experts/bookings; marketplace/adoption; lost/found; medical;
care; devices; places; and cross-cutting file/audit services. Active
compatibility classes are not dead merely because they retain `Prototype` or
`Preview` names.

### Resolved Stack And Dependency Baseline

| Surface | Declared / locked | Factual audit result |
| --- | --- | --- |
| PHP | `>=8.5 <8.6` / 8.5.8 | Boots; Intl, PDO SQLite, GD, Imagick present locally; direct extension requirements incomplete |
| Laravel | `^13.0` / 13.23.0 | Boots; 13.29.0 available but deferred to prompt 03 |
| Livewire | `^4.3.4` / 4.3.4 | Compatible; 4.4.2 deferred to prompt 03 |
| Tailwind | `^4.3.3` / 4.3.3 | Current stable line |
| Vite/plugin | 8.2.0 / 3.1.3 | Nano ID advisory requires targeted Vite patch; broader updates deferred |
| Pest/PHPUnit | 4.7.5 / 12.5.30 | Correct major line; canonical Artisan test process lacks required memory configuration |
| Larastan/PHPStan | 3.10.0 / 2.2.7 | Baseline passed at level 5 with 1 GiB |
| Lock state | Composer lock + npm lock only | CommonMark has six advisories; Nano ID has one high advisory using official npm registry |

### Critical Workflow Traces

| Workflow | Validation and authorization | Persistence / side effects | Current test evidence or gap |
| --- | --- | --- | --- |
| Registration | Livewire form; server-generated actor key; framework auth/session regeneration | User insert then framework `Registered` event | Auth and portal-boundary tests |
| Pet creation | Livewire form, duplicate review, policy and idempotency | Transaction: profile, manager, privacy, alias, lifecycle, audit; optional protected photo | Pet foundation/create/duplicate tests |
| Social mutation | `PerformActionRequest`, portal/auth/active middleware, Action authorization | Locked/versioned encrypted `UserDomainState` | Social persistence tests; decomposition deferred |
| Medical/care temporary access | Owner policy, authenticated active bearer, expiring hashed token, optional account binding, section/file permission and row lock | Downstream download/write succeeds inside the grant transaction before view/audit mutation; actual bearer is audited | Bound mismatch, unbound different-bearer, denied side-effect, download and shared-entry tests |
| Device command | Password confirmation, throttle, request, explicit `controlCommand` policy | Locked/idempotent command, state/event/audit transaction | Smart-device and real Gate tests; no global administrator bypass |
| Marketplace acceptance | Decimal-normalizing request, listing policy, scoped reservation, row locks | Reservation/listing transition, checked minor-unit total, immutable Order and audit | Precision, exact rental/deposit, maximum-width, rollback and call-site tests |
| Forum topic publication | Request plus runtime schema, policy, media normalization | Transactional topic/taxon/lifecycle creation and compensating media cleanup | Topic lifecycle/schema tests |

### Reproducible Initial Baseline

| Command | Observed result before repair |
| --- | --- |
| `composer validate --strict` | Pass |
| `composer audit --locked` | Fail: six `league/commonmark` 2.8.3 advisories |
| Official-registry `npm audit --package-lock-only --audit-level=high` | Fail: high Nano ID 3.3.16 advisory |
| `php artisan about` / uncached route discovery | Pass; 180 total / 169 non-vendor routes |
| `php artisan test --compact` | Fatal at 128 MiB in taxonomy/factory loading |
| `php -d memory_limit=1G artisan test ...` | Still fatal: Artisan child Pest process remains at 128 MiB |
| Direct Pest with `php -d memory_limit=1G` on factory/seeder suite | Pass: 1,791 tests / 5,313 assertions |
| `ForumAccessibilityTest` | Fail: 5 pass / 1 false-negative DOMXPath assertion / 49 assertions |
| `vendor/bin/pint --test` | Pass |
| `PAO_DISABLE=1 vendor/bin/phpstan analyse --memory-limit=1G --no-progress` | Pass, zero errors |
| `npm run build` | Pass; Vite 8.2.0, 670 ms |
| `php scripts/verify-fresh-database.php` | Pass: 139 migrations, 219 tables, repeat seed stable at five users |
| Forum requirement generator | Fatal at 128 MiB; with 1 GiB reports generated JSON stale |
| Forum source preservation | External failure: source entry `1785397895` is unavailable |
| Seeding coverage generator | Committed evidence stale: 203 models / 246 helpers / 1,521 enum states now |

### Accepted Findings And Execution Items

Every item names its dependency, owner, files, acceptance criterion, test,
verification command, status, and rollback. `Principal` is the sole editor.

| ID | Dependency | Owner | Files/modules | Acceptance criteria | Required test / verification | Status | Rollback |
| --- | --- | --- | --- | --- | --- | --- | --- |
| AUD-01 | None | Principal | Git/index and work ledger | Initial user slice remains byte-present and task commit contains only attributable work | Final status, staged/unstaged diffs, temporary-index staged diff | complete | Restore saved index tree, never working files |
| AUD-02 | AUD-01 | Seven read-only auditors | Entire first-party tree | All requested analysis reports reconciled; high-impact claims directly validated | Inventory commands and exact file/symbol sampling | complete | Documentation-only reversal |
| AUD-03 | AUD-02 | Principal | This plan and current-state audit | Stable implementation/deferred IDs exist before runtime edits | Diff proves plan precedes production changes | complete | Revert documentation section only |
| AUD-04 | AUD-03 | Principal | `AppServiceProvider`, `ChangeForumTopicState`, authorization tests | No blanket admin Gate bypass; private care/medical/device/order/search resources deny unrelated admins while explicit policy admin abilities remain | Red/green real-Gate authorization tests; policy/security slices | review-corrected; targeted verified | Restore hook only if equivalent explicit policy controls are added first |
| AUD-05 | AUD-04 | Principal | Care/medical/device grant resolvers, downloads and shared writes | Bound grants require the authenticated recipient; unbound grants log the actual authenticated bearer; mismatch/denial consumes nothing; authorization and shared care writes remain in one transaction | Bound and different-bearer tests, denied side-effect tests, transactional architecture check | review-corrected; targeted verified | Revert binding as one coherent slice; token hashing/expiry unchanged |
| AUD-06 | AUD-03 | Principal | `CreateOrder`, marketplace request validation, marketplace seeder, exact amount value object | No float money calculation; canonical decimals and schema-width totals remain exact; demo seeder fails closed outside allowed environments | Unit amount tests plus precision, quantity/rental/deposit/boundary/rollback/seeder regressions | review-corrected; targeted verified | Revert call sites and value object together; no schema change |
| AUD-07 | AUD-03 | Principal | Composer/npm manifests and locks | Targeted CommonMark and Nano ID advisories removed; required direct extensions and Node/package-manager floors declared; no broad upgrade | Composer validate/audit/platform; official npm audit; build | targeted verified; concurrent broad Composer slice excluded | Revert manifest/lock slice atomically |
| AUD-08 | AUD-03 | Principal | `phpunit.xml`, safe test/browser runners, forum generator/test and generated outputs | Canonical tests clear cached config and have 1 GiB; stateful browser gates own disposable SQLite/loopback runtime and direct runners fail closed; forum output is deterministic independently of external history | Cached-config runner smoke, browser refusal/isolation tests, generator `--check`, architecture tests | review-corrected; targeted verified | Revert config/runners/generated outputs atomically |
| AUD-09 | AUD-08 | Principal | Compliance/seeding generators and evidence | Point 13 supplemental rows survive generation; real cache use is not marked N/A; seeding evidence is byte-current | Generator byte-parity architecture tests | targeted verified | Regenerate from reverted generator |
| AUD-10 | AUD-03 | Principal | Forum accessibility test, expert booking/editor Blade and controller | Caption test asserts actual behavior; repeated inputs have labels; Blade receives prepared request data | Targeted feature/architecture/render tests and build | targeted verified | Revert each independent UI/test slice |
| AUD-11 | AUD-04..10 | Principal | `AGENTS.md`, generated inventory, audit/architecture/security/testing/seeding/deployment/index/review/changelog docs | Current claims are dated, per-path/symbol inventories are byte-current, canonical links agree, commands reproduce, no stale pass claim remains | Documentation generators, link/source scans, diff review | in progress | Revert task documentation only |
| AUD-12 | AUD-11 | Three independent reviewers | Frozen attributable diff | Audit, security/integrity, and test reviewers disposition every finding; valid findings fixed | Review ledger plus rerun targeted/full gates | review complete; corrections targeted verified | Revert finding-specific change if correction is unsafe |
| AUD-13 | AUD-12 | Principal | Complete repository | Applicable final gates pass or only exact external blockers remain; coherent main commit and safe push | Full verification list and Git evidence | pending | Revert task commit normally; never rewrite history |

### Independent Review Dispositions

- `AUD-RV-01` exact-inventory, direct SQLite-extension, device-binding,
  money-wiring, lock-provenance, and workflow-staleness findings were accepted.
  The deterministic inventory generator and per-path Markdown authority table,
  manifest architecture checks, behavioral regressions, targeted lock audit,
  and corrected workflow chains resolve them.
- `AUD-RV-02` premature grant consumption, marketplace demo-seeder safety,
  decimal precision/schema width, device binding coverage, and legacy JSON
  money compatibility findings were accepted and regression-tested. Child
  disclosure redesign remains assigned to prompts 09/10 because this pass did
  not alter that product boundary. Grant `actor_role` remains the scoped role
  named by the grant while `actor_key` is always the authenticated bearer; it
  is audit metadata, not identity authorization.
- `AUD-RV-03` care-write TOCTOU, unsafe browser mutation, cached-config test
  execution, missing real-Gate/unbound-bearer tests, masked forum generation,
  money branch coverage, generator drift, formatting, and stale-documentation
  findings were accepted. The transaction wrappers, disposable runners,
  split deterministic/external tests, expanded regressions, and synchronized
  evidence resolve them subject to `AUD-13` final gates.
- Codex `/review` is not available in this environment; the three required
  behavior-focused independent reviewers supplied the review boundary.

### Explicit Deferred Modernization Ownership

| Finding | Owning later prompt | Reason it is not an audit-time repair |
| --- | --- | --- |
| Broad stable dependency upgrades | 03 | Current prompt permits only advisory/platform blockers |
| Large Actions/services, prototype-state split, orphan candidates | 06 | Requires characterization and domain-by-domain migration |
| Missing schema relationships, upload/DB atomicity, forum pagination and vote bounds | 07, 20, 21 | Requires additive schema/query/file design and volume tests |
| Shared-browser forum/message/care offline draft isolation | 12, then 23 review | Requires versioned browser-storage migration and two-account browser coverage |
| Nested child non-disclosure and form service location | 09 and 10 | Requires consistent binding/error-contract changes |
| Large Livewire components, hydration/key gaps, direct Livewire persistence | 11 | Cross-component state and action extraction require dedicated tests |
| Tailwind/SCSS bundle modernization | 13 | Existing build is valid; visual migration requires measured proof |
| Remaining accessibility/browser-route coverage | 14 | Requires isolated connected browser matrix |
| Device-to-medical literals and native linguistic review | 15 | Localization-specific ownership and native review |
| Whole-schema factory/seed idempotency expansion | 16 and 17 | Needs explicit append-only table policy |
| Authenticated-by-default test foundation and CI | 18 and 23 | Broad test bootstrap/automation change |
| External providers/webhooks/payments | 19 and 22 | Provider selection/credentials and runtime process topology are absent |

Suspected plaintext moderation/mentorship fields and device timezone provenance
remain unclassified until the owning requirement is confirmed; no destructive
migration or speculative rewrite is authorized here.

## Active Revalidation: Prompt 01 Repository Audit And Foundational Repairs

Status: `plan saved; implementation in progress` on 2026-08-30.

This `AUD2` section records the current prompt-01 revalidation. It supplements,
rather than rewrites, the completed `AUD-*` history above. Production edits for
this revalidation begin only after this section is saved.

### Protected State And Governing Evidence

- The audit started on `main` at `fdaf7292a152ae61b85e17cf1ce69449d6d4292f`,
  aligned with `origin/main`, with a materially dirty shared tree containing a
  large staged audit/auth/forum/database/documentation slice and 18 additional
  unstaged paths. While discovery was read-only, concurrent repository work
  committed and pushed `f605d58` and `153ae45`; those commits are external to
  this revalidation and must not be claimed, reverted, or restaged here.
- The applicable repository instruction chain is the root `AGENTS.md`; no
  nested first-party `AGENTS.md` or `AGENTS.override.md` exists. `docs/index.md`
  is the documentation source-of-truth index. The existing
  `docs/implementation-plan.md` remains the one canonical global plan.
- The documentation auditor classified 350 repository Markdown files: 244
  non-tooling first-party documents and 106 repository skill/instruction mirror
  documents. Canonical, supporting, generated, historical, and tooling-mirror
  status must be emitted per path by `docs/audits/repository-inventory.md`.
  Repository-local skill examples that conflict with `AGENTS.md` are
  non-authoritative; the root contract wins.

### Current Inventory And Baseline

| Surface | Revalidated inventory / evidence |
| --- | --- |
| Runtime routes | 180 routes from `route:list --json`; 174 with `--except-vendor`; 167 `App\\` actions; all 179 audited non-Boost routes named, including unstable generated names found in the inventory generator |
| Application modules | 147 controllers, 9 middleware, 67 Form Requests, 226 Actions, 155 Services, 204 models, 47 policies, 1 API Resource, 1 provider, no first-party jobs/webhooks/outbound clients |
| Livewire / presentation | 36 renderable class components plus 49 form objects, 36 Livewire views, 357 Blade views, 246 anonymous Blade components, 9 JavaScript modules, 1 Tailwind entry and 31 SCSS files; no Volt, Flux, Filament, impure Blade, or duplicate Alpine |
| Persistence | 139 migrations, 218 named schema tables plus Laravel's migration ledger at runtime, 204 model factories plus `ApplicationFactory`, and 44 seeder files |
| Tests | 129 `*Test.php` files: 126 Feature and 3 Unit; 1,051 static Pest declarations, 6 datasets, no detected skip/todo markers, and five standalone browser commands |
| Localization / cache / process | 45 language files for each of `en`, `lt`, and `ru`; 10 sampled cache/lock consumers; database-backed queue and operator-managed deployment, with no scheduler or first-party queued jobs |
| Stack | PHP 8.5.8; Laravel 13.29.0; Livewire 4.4.2; Tailwind and `@tailwindcss/vite` 4.3.3; Vite 8.2.2; Laravel Vite plugin 3.2.0; Pest 4.7.8; PHPUnit 12.5.33; Larastan 3.10 / PHPStan 2.2.9; Node 26.4.0 / npm 12.0.1 |

Critical workflow traces remain the registration, pet creation, social-state,
medical/care/device temporary access, device command, marketplace acceptance,
and forum-publication chains recorded above. This revalidation additionally
traced forum category administration through
`AdminDashboard::saveCategory()`: validated Livewire state and component
authorization currently lead to two independent writes and cache invalidation,
without one Action-owned transaction. The accepted immediate repair makes that
chain authorize again inside a focused Action, lock and update the category and
translation atomically, then invalidate cache only after success.

Observed baseline: PHP/application boot, Composer strict validation/audit and
platform checks passed; `composer outdated --direct --strict` returned `1` only
for out-of-scope next-major alternatives; official-registry npm audit passed
with zero vulnerabilities while the configured mirror's audit endpoint returned
404; production Vite build passed; route/about commands passed; generated
repository and seeding evidence were stale; forum generation passed when rerun
serially; immutable forum-source preservation remains blocked by missing source
entry `1785397895`. An attempted parallel invocation of database-backed test
wrappers produced signal 11, so every authoritative PHP suite remains serial as
required by `AGENTS.md`.

### Accepted Findings, Repairs, And Deferred Ownership

| ID | Dependency | Owner | Affected paths/modules | Acceptance criteria | Required tests / verification | Status | Rollback |
| --- | --- | --- | --- | --- | --- | --- | --- |
| AUD2-01 | None | Principal | Git state and audit ledger | Shared staged/unstaged work and concurrent commits remain intact and attributable | Status, complete diffs, final task-only staged diff | complete | Never reset; unstage only task-owned paths if required |
| AUD2-02 | AUD2-01 | Seven read-only specialist auditors | Entire first-party tree | Required structured reports are reconciled; material findings are independently sampled | Framework inventories, exact symbol/schema/document checks | complete | Documentation-only correction |
| AUD2-03 | AUD2-02 | Principal | This plan | Stable dependencies, owners, acceptance, tests, status, and rollback exist before production edits | Git diff and ledger chronology | complete | Revert this additive section only |
| AUD2-04 | AUD2-03 | Principal | `scripts/generate-repository-inventory.php`, generated inventory, architecture tests | Two consecutive generations are byte-identical; generated route names cannot inject randomness; every first-party Markdown path has correct authority, including skill mirrors | Red/green deterministic-generator test, generator byte-parity test | implemented; focused test passed 1 test / 7 assertions | Revert generator/test and regenerate previous evidence |
| AUD2-05 | AUD2-03 | Principal | Forum category Action, `AdminDashboard`, focused forum tests | Category and translation update in one authorized locked transaction; cache invalidation occurs only after success; component delegates one operation | Red/green Action authorization/success/rollback-oriented tests and Livewire administration test | implemented; focused slice passed 16 / 86 | Revert Action/delegation together; no schema change |
| AUD2-06 | AUD2-03 | Principal | `AdoptionDemoSeeder`, `CollaborativeGuideDemoSeeder`, factory/seeder tests | Direct production invocation fails before any mutation; allowed environments retain deterministic behavior | Red/green production-denial tests and affected seeder tests | implemented; guard test passed 2 / 2 | Revert guard/tests together |
| AUD2-07 | AUD2-03 | Principal | Local `public/storage`, `storage`, `bootstrap/cache`, media/config regression | Prohibited public-storage symlink is absent; private-link config remains empty; runtime paths are owned by `www:www` | Red/green private-media test, exact `readlink`/`find` ownership checks | link removed and media test passed 10 / 25; final ownership check pending | Recreate link only if a future approved public-media ADR replaces the private boundary; restore documented owner if changed |
| AUD2-08 | AUD2-04..07 | Principal | Audit, architecture, seeding, review, limitations, compliance, changelog, plan | Counts, authority, commands, findings, limitations, and statuses describe only observed current state; generated evidence is byte-current | All documentation generators/checks and link/secret/diff review | pending | Revert task documentation; regenerate generated files |
| AUD2-09 | AUD2-08 | Principal | Complete repository | Targeted tests, serial full Pest, Pint, Larastan, isolated fresh migration/seed, official npm audit/build, cache and browser smoke checks pass or expose exact external blockers | Canonical commands recorded with observed counts/exits | pending | Revert only the failing attributable repair |
| AUD2-10 | AUD2-09 | Three new independent reviewers | Frozen task diff and adjacent boundaries | Audit-correctness, security/integrity, and regression reviewers disposition every behavior finding; valid findings are fixed and rerun | Review ledger plus post-fix targeted/full checks | pending | Revert finding-specific correction if unsafe |
| AUD2-11 | AUD2-10 | Principal | Task-owned Git slice | Coherent commit is created on `main`; push occurs only if origin remains safe and credentials work | Staged diff, `diff --check`, commit hash, push output, final status | pending | Normal revert commit only; never rewrite history |

The following evidence-backed findings are deferred to their existing owners:
browser-storage account isolation and JavaScript teardown to prompt 12;
Livewire monolith/key/offline-state work to prompt 11; nested-resource
non-disclosure and temporary-access revocation races to prompts 09 and 10;
care file/database atomicity and parent-qualified task integrity to prompts 07,
20, and 21; Composer extension and Node-floor normalization to prompt 03;
behavioral route coverage, opt-in test authentication, global outbound-request
prevention, and CI/coverage to prompts 18 and 23. These items require broader
contract or schema design and are not hidden as audit-time fixes.

## Active Delivery: Blade And Browser Lifecycle Modernization

Status: `discovery in progress` on 2026-08-30.

This delivery is governed by `SYS-FRONTEND-001`, `SYS-FRONTEND-002`,
`SYS-LIVEWIRE-001`, `SEC-WEB-002`, `TEST-ARCH-001`, `TEST-SECURITY-001`, and
the applicable accessibility, localization, responsive, and quality
requirements. Discovery is read-only; the principal agent owns integration,
production changes, final review dispositions, verification, publication,
and rollback decisions.

### Work Ledger

| ID | Subagent | Exclusive discovery scope | Expected output | Dependencies | Status |
| --- | --- | --- | --- | --- | --- |
| BLM-A1 | Blade Purity and Data-Flow Analyst | All first-party Blade data access, PHP, business calculations, permission/SEO logic, relation access, literals, and raw rendering | Violation inventory, traced callers, correct owners, architecture-test candidates, risks, and commands | Repository contract and canonical frontend/security documents | pending |
| BLM-A2 | Blade Component and Presentation Contract Analyst | Repeated cards, forms, tables, actions, status, modal, empty/error, navigation, and layout markup | Consolidation map, explicit props/slots/defaults/states, usage/test updates, risks, and commands | BLM-A1 evidence is informative but not blocking | pending |
| BLM-A3 | Flux Compatibility and Accessibility Analyst | Installed package/license evidence and every Flux-like or custom form/modal/menu/table/notification use | Capability matrix, invalid/deprecated list, retain/replace decision, accessibility tests, risks, and commands | Installed Composer/NPM metadata | pending |
| BLM-A4 | Alpine Integration Analyst | JavaScript entry points, packages, Livewire bootstrap, Alpine plugins/globals, `x-` directives, CSP/data boundaries | Ownership map, duplicate/conflict findings, migration/lifecycle tests, risks, and commands | Installed Livewire/Alpine metadata | pending |
| BLM-A5 | JavaScript Navigation Lifecycle Analyst | First- and third-party widgets, listeners, timers, observers, media, maps, editors, Vite loading, navigation/account transitions | Lifecycle registry, init/destroy risks, wrapper/browser requirements, risks, and commands | BLM-A4 runtime map is informative but not blocking | pending |
| BLM-A6 | Raw HTML and XSS Boundary Analyst | Blade raw echo, Markdown/rich text, email, preview, JSON-LD, SVG, URLs, script data, and third-party widgets | Origin-to-sink map, sanitizer controls, adversarial tests, risks, and commands | Canonical security and frontend rules | pending |
| BLM-A7 | Frontend Architecture Test Analyst | Existing Pest architecture suite, Blade tree, package metadata, static checks, fixtures, and false-positive exclusions | Maintainable check specification, fixture strategy, exceptions, risks, and commands | Findings from BLM-A1/A3/A4/A6 are informative but not blocking | pending |
| BLM-R1 | Blade Architecture Reviewer | Final changed views and their PHP preparation boundaries | Independent severity-ranked findings and exact failure scenarios | Implementation freeze | pending |
| BLM-R2 | Flux and Accessibility Reviewer | Final Flux/custom components, forms, focus, keyboard, themes, translations, and tests | Independent compatibility/accessibility findings and verified usage list | Implementation freeze | pending |
| BLM-R3 | JavaScript Lifecycle and XSS Reviewer | Final modules, Alpine, raw output, URLs, widgets, teardown, and browser tests | Independent lifecycle/XSS attack findings and reproductions | Implementation freeze | pending |

The discovery reports will be reconciled into implementation items in this
section before production code changes begin. Analysts and reviewers are
read-only unless a later ledger revision delegates one narrowly isolated fix.

## Active Work Ledger: Tailwind CSS 4 And Design System

Status: `discovery in progress` on 2026-08-30. This ledger is the coordination
boundary for the repository-wide Tailwind CSS-first migration. All discovery
and review agents are read-only; the principal agent owns reconciliation,
implementation, tests, documentation, Git integration, and publication.

| ID | Agent | Exclusive scope | Expected output | Dependencies | Status |
| --- | --- | --- | --- | --- | --- |
| TW13-WL-A1 | Tailwind Upgrade and Configuration Analyst | Package metadata, NPM lock, Vite, Tailwind/PostCSS config, CSS entries, plugins, presets, scripts, Node compatibility | Configuration migration map, dependency changes, visual risk and rollback notes | Repository contract and canonical frontend/Tailwind docs | pending |
| TW13-WL-A2 | Tailwind Source Detection and Dynamic Class Analyst | Blade, PHP class maps, Livewire, JavaScript, CSS sources, vendor templates, safelists | Source registry, unsafe dynamic-class findings, explicit-map and build-test needs | Repository contract and canonical Tailwind rules | pending |
| TW13-WL-A3 | Design Token and Theme Architect | Brand/theme values, colors, typography, spacing, breakpoints, containers, radii, shadows, z-index, motion, component variants | Token inventory, target `@theme` model, repeated-value migration and contrast tests | Product and design documents | pending |
| TW13-WL-A4 | Responsive Layout Analyst | Public/auth layouts, navigation, sidebars, cards, grids, tables, forms, filters, dialogs, drawers, media, charts, pagination | Screen/component matrix, prioritized defects, container-query and layout recommendations | Frontend, accessibility, localization, and active feature contracts | pending |
| TW13-WL-A5 | Tailwind Accessibility Styling Analyst | Forms, controls, links, badges, alerts, dialogs, menus, tables, loading/disabled/error states, themes | Accessibility-style findings, utility/token changes, verification scenarios | WCAG and repository accessibility contracts | pending |
| TW13-WL-A6 | Modern Tailwind Feature Applicability Analyst | Installed Tailwind 4 capabilities and reusable component/layout opportunities | Feature matrix with approved locations and rejected candidates/reasons | Exact installed version and browser contract | pending |
| TW13-WL-A7 | CSS Duplication and Component Abstraction Analyst | CSS/SCSS, Blade class lists, components, `@apply`, arbitrary values, specificity and dead CSS | Duplication plan, dead-CSS candidates, component/token/utility decisions | Source-detection and token findings | pending |
| TW13-WL-A8 | Frontend Build and Visual Verification Analyst | Build scripts/output, manifests, asset sizes, browser/visual tooling and critical pages | Build baseline, critical visual checklist, regression-test and screenshot plan | Existing dependencies and browser runners | pending |
| TW13-WL-R1 | Tailwind Architecture Reviewer | Final package/config/CSS/source/token diff and production output | Severity-ranked findings and release-readiness verdict | Implementation freeze and final diff | pending |
| TW13-WL-R2 | Responsive UI Reviewer | Final critical screens across widths, locales, zoom, touch and keyboard | Reproducible responsive findings and required fixes | Built assets and isolated browser fixture | pending |
| TW13-WL-R3 | Accessibility Styling Reviewer | Final focus, contrast, status, motion, forced-colors, touch and theme states | Reproducible accessibility findings and verified state checklist | Built assets and isolated browser fixture | pending |
| TW13-WL-R4 | Build Output Reviewer | Final lock/config/manifest/assets and critical generated selectors | Build findings, size comparison and release recommendation | Clean production build | pending |

## Active Work Ledger: Complete Localization And Hardcoded Text Removal

Status: `discovery in progress` on 2026-08-30. The `LC15-*` identifiers are
the exclusive coordination boundary for this repository-wide localization
delivery. Analysts and reviewers work read-only; the principal agent owns the
canonical plan, implementation, tests, documentation, Git integration, and
publication. Independent scopes run in waves because the shared agent pool has
four total slots.

| ID | Agent | Exclusive scope | Expected output | Dependencies | Status |
| --- | --- | --- | --- | --- | --- |
| LC15-A1 | Locale Architecture and Routing Analyst | Locale configuration, middleware, routes, sessions/cookies, user preferences, language files, database-translated content, HTTP/Livewire/mail/notification/API/job locale flow | Locale architecture map; canonical locales/fallback; routing, persistence, invalid-locale and RTL findings; tests and commands | Repository contract and canonical architecture, security, frontend, Livewire, and localization documents | pending |
| LC15-A2 | Hardcoded String and Translation-Key Analyst | PHP, Blade, Livewire, JavaScript, validation, exceptions, notifications, mail, API output, accessibility, SEO, fixtures, and tests | Classified literal inventory; stable-key migration map; intentional nonlocalized exceptions; implementation order and scanner tests | Existing localizer scripts and translation conventions | pending |
| LC15-A3 | Translation Consistency and Placeholder Analyst | All EN/LT/RU catalogues, JSON translations, validation, mail/notification templates, pluralization, placeholders, nesting, escaping, terminology, and dead keys | Locale parity report; mismatch inventory; consolidation and human-review recommendations; representative plural tests | Canonical locale tree and current translation references | pending |
| LC15-A4 | Validation, Notification, Mail, and API Localization Analyst | Form Requests, Livewire validation, exceptions, notifications, mailables, deferred side effects, user-facing JSON and provider failure mapping | Communication localization matrix; recipient-locale and serialization defects; required fixes and tests | Locale architecture evidence and Laravel communication boundaries | pending |
| LC15-A5 | Locale-Aware Formatting Analyst | Dates, times, relative time, timezone, numbers, percentages, currency, lists, measurements, coordinates, exports, reports, JavaScript and Blade formatting | Formatting ownership policy; direct-format inventory and migration map; locale/timezone/currency edge tests | Installed Intl/framework capabilities and existing formatter service | pending |
| LC15-A6 | Localized Content and SEO Analyst | Public portal pages, route locale strategy, titles/descriptions, canonical and alternate metadata, Open Graph, JSON-LD, database translations and authored-content boundaries | Public-content/SEO matrix; indexing/fallback/escaping defects; applicable and not-applicable tests | Locale architecture and authenticated-portal contract | pending |
| LC15-A7 | Localization Test and Automation Analyst | Pest/architecture/browser suites, scanner scripts, factories, seeders, critical routes, long/Unicode/RTL fixtures and deterministic timezones | Test/automation plan; coverage gaps; exact commands; scanner false-positive controls; long-text/RTL decision | Findings from current tests and repository scripts | pending |
| LC15-R1 | Translation Coverage Reviewer | Final changed source, locale catalogues, notifications, mail, API errors, accessibility/SEO strings, tests and scanners | Severity-ranked literal/key/placeholder/escaping findings with exact locations and failure scenarios | Frozen attributable diff and completed implementation | pending |
| LC15-R2 | Locale Behavior and Formatting Reviewer | Final locale selection/persistence/fallback, timezone/number/currency/plural behavior, recipient locale and deferred work | Severity-ranked behavior findings and locale-architecture readiness verdict | Frozen attributable diff and completed targeted checks | pending |
| LC15-R3 | Localization Regression and UX Reviewer | Critical pages/components/forms/errors across EN/LT/RU, long/Unicode content, responsive layouts and accessibility labels | Severity-ranked mixed-language, clipping, terminology and journey findings with exact pages/locales | Built assets, deterministic fixtures and connected browser environment | pending |

## Current Delivery: General Pet Size Category

Status: `implemented and release-verified` on 2026-08-04.

- Store one nullable controlled category on the canonical pet profile without
  inferring a default from species, breed, image, weight, or legacy text.
- Reuse one server normalizer through the existing authorized Appearance
  Action while preserving optimistic locking, idempotency, audit, cache
  invalidation, no-op behavior, and omitted-input compatibility.
- Render an accessible EN/LT/RU editor and query-free public projection that
  explicitly distinguishes the category from measurements and medical facts.
- Add the profile-side `(size_category, status, id)` index without pretending
  a marketplace, place, event, service, carrier, product, or search consumer
  has been delivered.
- Keep exact measurements and public, household, or clinical weight privacy
  outside this nine-requirement package.

Exact scope and observed evidence belong in
`docs/plans/pet-profile-size-category-work-package.md`.

## Active Interface Continuation: Owner Profile

Status: `implemented and release-verified` on 2026-08-04.

- Keep `/@mia-carter` as a deliberate authenticated profile hero while moving
  its complete first-party and system surface into one 131-leaf EN/LT/RU
  contract.
- Keep tab and audience state locale-independent, prepare all routes, actions,
  privacy values, copy, and icon names in the presenter, and leave Blade as a
  passive renderer.
- Add one canonical Lucide icon language to the hero, tabs, audience preview,
  overview sections, badges, and safety controls.
- Skip pet and moment queries for tabs that do not render those collections;
  the `about/friend` projection now issues one state query and no pet-profile
  query instead of seven total queries.
- Retain 44-pixel targets, keyboard focus, reduced motion, forced-colors
  usability, EN/LT/RU parity, and zero horizontal overflow from 320 to 1920
  pixels.
- Preserve this browser ratchet while continuing through the remaining
  deliberate detail/workspace profiles; exact-tree Pint, Larastan, 3,055-test
  Pest, dependency, migration, seed, cache, source-preservation, route, icon,
  and diff gates passed before publication.

## Previous Delivery: Structured Pet Identifying Marks

Status: `implemented and release-verified` on 2026-08-04.

- Store up to twelve ordered identifying marks as normalized child rows with
  stable keys, controlled types, encrypted descriptions, actor attribution,
  visibility, and reversible retirement.
- Reuse one server normalizer and one synchronizer through the existing
  authorized Appearance Action while preserving optimistic locking,
  idempotency, audit, cache invalidation, and omission compatibility.
- Offer only public and private-verification visibility until friend, clinic,
  and active-search consumers have authoritative access checks.
- Render an accessible EN/LT/RU manager editor and eager-load only active
  public rows for the public profile, with a second presenter-side filter.
- Preserve the legacy encrypted free-text value as private compatibility data.

Exact scope and observed evidence belong in
`docs/plans/pet-profile-identifying-marks-work-package.md`.

## Previous Delivery: Species-Aware Pet Body Covering

Status: `implemented and release-verified` on 2026-08-04.

- Select coat, feather, scale, skin, mane, and shedding controls from the
  existing broad species without trusting browser-submitted applicability.
- Reuse one server normalizer across progressive and durable compatibility
  mutation paths while retaining authorization, optimistic locking,
  idempotency, audit, and cache invalidation.
- Store the schema-versioned object in the existing encrypted profile payload,
  reuse structured scale-color clarification, and add no migration or query.
- Render EN/LT/RU workspace and public projections while keeping the bounded
  skin observation manager-only.
- Keep search, recommendations, care, groomer/shelter/finder consumption,
  private marks, measurements, identity media, and medical facts outside this
  11-requirement package.

Exact scope and observed evidence belong in
`docs/plans/pet-profile-body-covering-work-package.md`.

## Previous Delivery: Pet Appearance Color

Status: `implemented, release-verified, and published` on 2026-08-04.

- Store one controlled primary color, up to four unique additional colors,
  three controlled patterns, and bounded species-neutral clarification in the
  existing encrypted profile payload.
- Reuse one server normalizer across progressive and durable compatibility
  mutation paths while retaining authorization, optimistic locking,
  idempotency, audit, and cache invalidation.
- Render EN/LT/RU workspace and public projections through one presenter with
  no new query, migration, or Blade business logic.
- Preserve legacy free text and keep identifying marks out of the public
  projection.
- Keep automatic lost/found descriptions, coat, marks, measurements, identity
  media, and cross-domain consumption outside this 12-requirement package.

Exact scope and observed evidence belong in
`docs/plans/pet-profile-appearance-color-work-package.md`.

## Previous Delivery: Pet Life Stage

Status: `implemented, release-verified, and published` on 2026-08-04.

- Derive newborn, juvenile, young, adult, senior, or unknown at read time from
  the existing honest age range and controlled animal-group thresholds.
- Keep separate dog, cat, bird, rabbit, rodent, fish, reptile, and horse
  boundaries; never use dog thresholds as a global fallback.
- Store only an authorized manual clarification with actor and time, and label
  it separately from an automatic result or medical verification.
- Reuse one server normalizer in progressive and durable compatibility update
  paths, retaining authorization, optimistic locking, idempotency, audit, and
  cache invalidation.
- Render EN/LT/RU workspace and public projections with no added query or
  private provenance disclosure.

Exact scope and final observed evidence belong in
`docs/plans/pet-profile-life-stage-work-package.md`.

## Previous Delivery: Pet Breed Origin And Provenance

Status: `implemented, verified, and published` on 2026-08-04.

- Preserve the legacy breed string as a bounded compatibility snapshot while
  storing an explicit one, mixed, possible-multiple, no-breed, or unknown
  overall state and up to four normalized origins.
- Keep confidence, information source, and optional mixed percentage separate
  from the breed value; a photograph cannot upgrade a reported or suspected
  origin to confirmed.
- Reuse one server normalizer and one owned-relation synchronizer across
  creation, generic update, progressive update, autosave, and manual save.
- Render localized trust/provenance controls and an honest public projection
  without queries or business logic in Blade.
- Keep taxonomy ingestion/verification, protected evidence documents,
  breed-based discovery, behavior, health, ownership, and lost/found effects
  outside this 35-requirement package.

Exact scope and observed evidence are recorded in
`docs/plans/pet-profile-breed-origin-work-package.md`.

## Current Delivery: Pet Name Identity

Status: `implemented, verified, and published` on 2026-08-03.

- Keep `pet_profiles.name` as the current canonical name and preserve all stable
  identity and adjacent-domain links during rename.
- Store typed nickname, previous, shelter, official, localized, and responds-to
  alternatives with normalized uniqueness, attribution, locale, and explicit
  visibility.
- Preserve the old current name automatically after a successful rename.
- Search only viewer-visible alternatives inside the existing policy-scoped pet
  workspace and expose only public alternatives on the public profile.
- Keep cross-domain name propagation, global/public alias discovery, merge,
  ownership, and taxonomy verification outside this package.

Exact scope and observed evidence are recorded in
`docs/plans/pet-profile-name-identity-work-package.md`.

## Previous Delivery: Honest Species Confidence

Status: `implemented, verified, and published` on 2026-08-03.

- Preserve the controlled broad species used by search and integrations.
- Store confirmed, possible, or unidentified confidence separately.
- Allow possible identification only for cat and dog and normalize every
  browser-controlled combination again in the Action.
- Render possible species honestly in every selected public, workspace,
  invitation, and duplicate projection.
- Keep adoption, found-animal coordination, ownership, and taxonomy
  verification outside this package.

Exact scope and observed evidence are recorded in
`docs/plans/pet-profile-species-confidence-work-package.md`.

## Current Delivery: Pet Duplicate Review And Access Requests

Status: `implemented, verified, and published` on 2026-08-03.

- Pause canonical pet creation on bounded, policy-visible name/species matches
  and expose only the safe identity/photo projection.
- Bind the explicit different-animal decision to an encrypted expiring review
  token that the creation Action verifies again.
- Store typed access requests with encrypted evidence, unique active/replay
  keys, reviewer attribution, and optimistic state.
- Grant no immediate capability; ordinary approval creates the existing
  invitation and requester acceptance activates it.
- Keep ownership transfer outside generic approval and leave organization
  attribution open until its relationship is authoritative.

Exact scope, observed gates, and remaining non-goals are recorded in
`docs/plans/pet-profile-duplicate-access-work-package.md`.

## Pass 1: Protect And Baseline

Status: `verified`

- Inspected branch, remote, tracked/untracked state, locks, runtime,
  dependencies, routes, schema, source, tests, and first-party Markdown.
- Preserved the pre-existing untracked `.agents/vendor/` tree.
- Captured Composer/NPM audits, full Pest baseline, Vite assets, routes,
  migrations, seed repeatability, and the absence of a coverage driver.
- Recorded the accidental local SQLite rebuild without concealing its impact.

Evidence: `docs/current-state-audit.md`.

## Pass 2: Canonical Documentation And Requirements

Status: `verified`

- Established `docs/index.md` and canonical product, system, non-functional,
  architecture, domain, data, security, authorization, frontend, Livewire,
  Tailwind, accessibility, localization, testing, seeding, performance,
  caching, integration, deployment, operations, audit, review, and limitation
  documents.
- Preserved historical feature specifications and plans while making their
  prototype-only authority explicit.
- Normalized 165 stable active requirement identifiers.
- Generated one traceability row per requirement and one factory row per model.

Verification:

```bash
php scripts/generate-compliance-matrix.php
php scripts/generate-seeding-coverage.php
php artisan test --compact tests/Feature/ArchitectureComplianceTest.php
```

## Pass 3: Runtime And Dependencies

Status: `verified`

- Constrained PHP to `>=8.5.0 <8.6.0` and Laravel to `^13.0`.
- Added Livewire 4.3 class-based components and Larastan 3.10/PHPStan 2.2.
- Updated Vite to 8.2 and retained Tailwind/Vite integration 4.3.3.
- Preserved Pest 4 as the intentional primary test style.
- Kept the single NPM lock and added no speculative infrastructure package.

Verification: Composer validation/audit/why-not/outdated, NPM audit/outdated,
application boot, Larastan, Pest, and production build.

## Pass 4: Identity, Authentication, And Authorization

Status: `verified` for the persisted application boundary

- Added immutable unique actor keys, account locale/timezone/status, and an
  explicit administrator capability through an additive migration.
- Added class-based Livewire login, registration, password reset, password
  confirmation, and account forms with separate Blade templates.
- Bound ownership to the authenticated actor rather than a fixed prototype
  identity.
- Protected mutations and private medical, care, device, order, booking, and
  coordination routes.
- Added active-account middleware, rate limits, signed verification, session
  regeneration/invalidation, policy checks, and environment-gated demo users.
- Added Laravel's fresh-password step-up contract to precise device pages and
  remote device commands; failed or missing confirmation produces no command.

## Pass 5: Localization And Passive Presentation

Status: `verified`

- Added validated `en`, `lt`, and `ru` locale handling with `en` fallback.
- Extracted static Blade, PHP, JavaScript, validation, and notification text
  to stable keys; the PHP localizer reports zero remaining eligible literals.
- Replaced dynamic count/status sentences with placeholder/plural-aware
  `presentation.php` keys.
- Added architecture checks for static and interpolated Blade literals,
  locale-key parity, placeholder parity, localized auth pages, and validation.
- Compiled every Blade template after migration.

Lithuanian and Russian catalogues are structurally complete and pass key and
placeholder parity. Native linguistic review is an external editorial step,
not an implementation gap.

## Pass 6: Data, Queries, Factories, And Seeders

Status: `verified`

- Added the identity migration and a reversible migration providing a leading
  index for every previously uncovered foreign key.
- Enabled strict Eloquent behavior outside production and repaired model
  projections exposed by it.
- Added valid factories for all 66 first-party Eloquent models through a typed
  application factory base and explicit model factories.
- Added 23 explicit helper states and verified 412 enum-backed states.
- Made `DatabaseSeeder` repeatable and production-gated.
- Added an opt-in, deterministic, production-blocked performance seeder.
- Added safe fresh-database verification using an asserted system temporary
  SQLite path.
- Added bounded query checks for care journals and smart devices.

Verification:

```bash
php artisan test --compact tests/Feature/Database
php scripts/verify-fresh-database.php
```

## Pass 7: Livewire, Tailwind, Accessibility, And Browser Behavior

Status: `verified`

- Kept Livewire class-based and multi-file; Volt remains prohibited and absent.
- Added minimal typed/form-object state, validation, precise loading targets,
  dirty/offline feedback, and server-authoritative auth actions.
- Added explicit Tailwind source detection and design tokens while preserving
  the mature SCSS component layer.
- Added visible focus, reduced-motion and forced-colors behavior.
- Added forum-wide focusable validation summaries with field-linked errors,
  semantic data-table captions, accessible media description/transcript/WebVTT
  contracts, text map alternatives, 44-pixel controls, contrast assertions,
  and a dependency-free Chrome desktop/mobile/reflow smoke runner.
- Corrected 320 px booking overflow and verified representative viewport
  widths, one logical `h1`, auth focus behavior, and a clean browser console.
- Completed the server-rendered place contract with validated deterministic
  pagination, preserved filter URLs, source/freshness/history coverage, and a
  guarded emergency clinic mode with direct call and route actions.
- Replaced publication-photo new-tab enlargement with one progressively
  enhanced PhotoSwipe viewer. Stable server-resolved photo keys now scope
  shared policy-authorized reactions and escaped comments to each individual
  photo through indexed relational records and a one-member/one-photo unique
  constraint. The shared gallery component provides localized zoom,
  keyboard/touch navigation, URL deep links, focus restoration, and responsive
  bottom/side social panels.
- Added durable `PetProfile` records and encrypted, versioned
  `UserDomainState` persistence so social mutations survive sessions without
  exposing browser-controlled authority.
- Added a durable IndexedDB care queue with source time, idempotency, conflict
  metadata, and duplicate-safe synchronization.

## Pass 8: Security And Quality

Status: `verified` for implemented boundaries

- Repaired fixed-actor authorization, guest access to private domains, session
  fixation/logout behavior, environment-gated demo identities, private media
  authorization, idempotent mutations, sensitive serialization, and baseline
  browser headers.
- Added architecture guards for Blade, Volt, environment reads, database
  shortcuts, route shape, model factories/fillable fields, localization, and
  debug leakage.
- Adopted Laravel 13's first-party image component through one
  `StorePublicImage` Action for every public photo-upload seam, with bounded
  input dimensions, EXIF orientation, WebP output, generated names, localized
  failures, and focused HTTP/action regressions.
- Removed meaningless framework example tests.
- Formatted all PHP and resolved Larastan level 5 findings without a baseline.

Provider-specific SSRF, webhook, media transport, and redacted integration-log
tests remain not applicable until those entry points exist.

## Pass 9: Final Verification

Status: `verified`

Completed:

- full serial Pest: 696 passed, 31,698 assertions;
- full parallel Pest: 696 passed, 31,698 assertions;
- Larastan: zero errors;
- production Vite build;
- Blade compilation;
- Composer/NPM security audits;
- isolated fresh migration and repeat seed;
- config, route, and view cache build plus application boot;
- syntax, dependency, translation, and generated-matrix checks;
- connected browser desktop/mobile/auth/private-flow review;
- expected coverage failure caused only by the missing PCOV/Xdebug driver.

Final staged-diff verification is part of Pass 10 because it must inspect the
exact temporary Git index used for publication.

## Pass 10: Publication

Status: `verified`

The canonical Markdown has been synchronized, evidence matrices regenerated,
and generated browser artifacts removed. Publication used a temporary Git
index that excluded the pre-existing `.agents/vendor/` tree. The exact staged
diff passed `git diff --cached --check`, the coherent modernization commit was
created on `main`, and the observed push advanced `origin/main`.

No blocked or not-applicable requirement may be described as implemented in
the release report.

## Current Delivery: Forum Topic-Type Schema Runtime

Status: `verified` on 2026-08-03

- Extract one typed catalogue for system field schemas and capability rules.
- Resolve active definitions through one bounded, versioned cache with model
  and synchronization invalidation.
- Enforce location, species, attachment, answer-rating, accepted-answer, and
  notification constraints at HTTP and direct Action boundaries.
- Persist the resolved definition ID and schema version on generic topic
  create/update while preserving existing structured data.
- Promote only the exact 20 scoped Phase 3 IDs after the complete gate passes;
  leave the 13 migration/final-audit IDs open.

The complete gate passed 2,360 tests and 78,407 assertions, full Pint and
Larastan, dependency audits, Vite/cache compilation, isolated migration and
repeat seed, immutable source preservation, and deterministic generation.

The executable contract and stop conditions are maintained in
`docs/plans/forum-topic-type-schema-runtime-work-package.md`.

## Current Delivery: Complete Phase 3 Migration Verification

Status: `implemented and verified` on 2026-08-03

- Audit every first-party migration for typed `up()`/`down()` methods and raw
  SQL escape hatches.
- Apply all migration filenames to an asserted disposable SQLite database,
  compare the ledger, roll every migration back, and reapply the exact set.
- Run the complete production-safe seed twice after reapplication and require
  stable identities.
- Reuse schema-integrity, constraints, enums/casts, factories, populated
  compatibility, and package rollback tests instead of adding a speculative
  migration.
- Passed the focused 2-test/11-assertion contract, the related
  1,611-test/4,795-assertion persistence slice, and the complete sequential
  2,362-test/78,760-assertion suite.
- Passed full Pint/Larastan, dependency audits, production build, cache
  compilation, fresh migration/repeat seed, complete rollback/reapply, source
  preservation, and deterministic requirement generation.

Exact scope, stop conditions, and final evidence are in
`docs/plans/forum-phase3-migration-verification-work-package.md`.

## Current Delivery: Phase 4 Before-Ownership Category

Status: `implemented and verified` on 2026-08-03

- Validate the complete source-derived category manifest before runtime use:
  checksum, schema version, exact totals, root sequence, required fields,
  stable-key/slug formats, hierarchy prefixes, and global uniqueness.
- Prove exact source-to-manifest-to-database metadata and ordering for category
  21 while leaving its two Phase 5 taxonomy labels unpromoted.
- Move database readiness checks into the locale-tree cache miss, reducing a
  warm localized read from 2 database queries to 0.
- Passed the focused 13-test/38-assertion contract, related
  72-test/5,949-assertion slice, and complete sequential
  2,384-test/78,891-assertion suite plus every applicable release gate.

Exact scope, exclusions, and evidence are in
`docs/plans/forum-phase4-before-ownership-category-work-package.md`.

## Current Delivery: Phase 4 Special-Needs Category

Status: `implemented and verified` on 2026-08-03

- Prove the complete category-22 source-to-manifest-to-database hierarchy,
  including 54 exact ordered children and all system-category locale rows.
- Require reviewed target/fallback translations in the category tree and root
  selector so unfinished translations cannot replace trusted source text.
- Preserve the existing locale cache/invalidation contract with zero added
  queries and zero warm-tree database statements.
- Passed the focused 4-test/17-assertion contract, related
  46-test/36,051-assertion slice, and complete sequential
  2,396-test/79,143-assertion suite plus every applicable release gate.

Exact scope, exclusions, and evidence are in
`docs/plans/forum-phase4-special-needs-category-work-package.md`.

## Current Delivery: Phase 4 Wildlife-Coexistence Category

Status: `implemented and verified` on 2026-08-03

- Prove the complete category-23 source-to-manifest-to-database hierarchy,
  including its exact root metadata and all 55 ordered source labels.
- Prove exact synchronized root/child stable keys, slugs, positions, and
  reviewed EN/LT/RU root rows without changing production behavior.
- Retain the wildlife-crime and roadkill reporting labels in the immutable
  source list while keeping their `forum.moderation.0010/.0011` requirements
  open in Phase 7.
- Passed the focused 2-test/12-assertion contract, related
  31-test/36,368-assertion slice, and complete sequential
  2,484-test/80,398-assertion suite plus every applicable release gate.

Exact scope, exclusions, and evidence are in
`docs/plans/forum-phase4-wildlife-coexistence-category-work-package.md`.

## Current Delivery: Phase 4 One-Health Category

Status: `implemented and verified` on 2026-08-03

- Prove the complete category-24 source-to-manifest-to-database hierarchy,
  including its exact root metadata and all 42 ordered source labels.
- Add the missing localized boundary explaining that One-Health discussions do
  not replace a physician, veterinarian, public-health authority, or emergency
  service; render it only for the selected root or child category.
- Version the localized category-tree cache payload without adding a database
  statement, route, schema, model, Policy, or parallel taxonomy.
- Passed the focused 3-test/21-assertion contract, related
  47-test/36,632-assertion slice, complete sequential
  2,586-test/81,835-assertion suite, desktop/mobile Chrome audit, and every
  applicable release gate.

Exact scope and evidence are in
`docs/plans/forum-phase4-one-health-category-work-package.md`.

## Current Delivery: Unified Forum Topic Editor

Status: `implemented and verified` on 2026-08-03

- Replaced the detached right sidebar on `/forum/ask` with one coherent editor
  shell whose complete five-item publishing guidance precedes the form.
- Reorganized the unchanged authoring controls into three labelled context,
  response, and optional-media sections without changing authorization,
  validation, persistence, taxonomy interaction, or query count.
- Added reviewed EN/LT/RU presentation copy and responsive desktop/mobile
  styling with semantic headings, visible control states, and 44-pixel mobile
  targets.
- Passed the focused 2-test/33-assertion contract, complete isolated
  2,588-test/82,043-assertion suite, full Pint/Larastan, dependency audits,
  production Vite build, compiled views, and desktop/mobile Chrome audit.

Exact scope and evidence are in
`docs/plans/forum-topic-editor-redesign-work-package.md`.

## Current Delivery: Progressive Pet Profile Completion

Status: `implemented and verified` on 2026-08-03 for
`pet.creation.0036-pet.creation.0058` only.

- Preserved `/compose/pet` as a compatibility redirect to the canonical
  minimal private-draft creation screen and moved the subsequent workflow into
  twelve ordered URL-backed steps.
- Added one central responsive navigator, only one active body, independent
  step saves, mutation-free skipping, purpose explanations, and text-based
  saved/optional state without a disclosure score.
- Added allowlisted optimistic partial updates, active-step relationship
  loading, one reusable current-manager policy projection, and bounded
  navigation existence queries.
- Stored microchip readiness and an optional identifier as one private,
  encrypted, versioned fact guarded by `change-microchip`; unauthorized roles
  receive no value, completion signal, form field, or mutation control.
- Verified EN/LT/RU parity, responsive desktop/mobile/320px rendering,
  keyboard/focus/console contracts, 130-migration fresh/rollback/reapply and
  repeat seed, dependency/static/build/cache gates, and the final serial suite
  of 2,657 tests and 84,589 assertions.

Exact scope and open follow-up requirements are in
`docs/plans/pet-profile-progressive-completion-work-package.md`.

## Current Delivery: Pet Profile Draft Autosave

Status: `implemented and verified` on 2026-08-03 for
`pet.creation.0071-pet.creation.0081` with dedicated atomic evidence.

- Added change/blur-driven saves to the seven ordinary descriptive steps while
  retaining the manual submission path.
- Rejected unknown and inactive step parameters before mutation and retained
  the existing form validation, managed-profile policy, allowlist, row lock,
  optimistic version, audit, lifecycle evidence, and cache invalidation.
- Added a locked idempotency key that rotates only after a successful response,
  plus one reusable accessible save-status component and explicit temporary
  photo unsaved state.
- Added page-memory reconnect recovery: a numeric form revision marks pending
  input, `online` retries the same ordinary Livewire action once, and only a
  matching server confirmation clears that revision. No profile value is
  stored in browser persistence.
- The focused progressive suite passes 27 tests and 159 assertions, including
  all-step wiring, persistence after a fresh mount, validation-key stability,
  six bounded client-revision acknowledgement cases, no-op replay, and
  mismatched-step non-mutation.
- The integrated current tree passed full Pint, zero-error Larastan, the
  isolated 2,692-test/85,091-assertion reconnect suite, the final complete
  2,695-test/85,875-assertion current-tree suite, production Vite build, cache
  smoke,
  dependency audits, and authenticated EN/RU/LT browser verification including
  a real failed network request, one automatic reconnect retry, reload, and
  value-restoration cycle.

Exact scope, evidence, and non-goals are in
`docs/plans/pet-profile-draft-autosave-work-package.md`.

## Current Delivery: Unified Icon System

Status: `implemented and verified` on 2026-08-03.

- Audited every first-party Blade template, direct/dynamic Lucide call, legacy
  size class, inline SVG, raw pictogram, foreign icon system, and native
  interactive candidate.
- Added `x-ui-icon` as the single size, stroke, fill, color, and ARIA primitive
  plus a downward-only executable audit.
- Migrated all 698 direct calls across 146 files, reduced dynamic debt from 83
  to zero, migrated 41 legacy SCSS selectors, removed all 310 legacy class
  attributes, and removed the last raw pictogram.
- Added prepared icons to all thirteen desktop primary-navigation destinations
  while retaining visible labels and current-page semantics.
- Added icons to 45 unambiguous actions; the remaining 52 candidates were
  reviewed and recorded as intentional text/content controls. Static debt is
  zero.
- Passed 2,639 Pest tests / 83,214 assertions, full Pint, Larastan over 1,385
  files, dependency audits, production build, cache compilation, fresh and
  repeat seeding, and a 33-screenshot EN/LT/RU browser matrix from 320 through
  1920 pixels without overflow or console errors.

The factual baseline is `docs/audits/icon-system-deep-audit.md`; the unlimited
execution ledger is `docs/plans/icon-system-unlimited-plan.md`.

## Current Delivery: Event Schedule Foundation

Status: `implemented and verified` on 2026-08-03

- Added occurrence-scoped tracks, rooms, sessions, and staff assignments with
  reversible schema, indexes, encrypted private data, enum state, factories,
  and repeat-safe demo records.
- Added one row-locked, idempotent schedule Action with policy checks,
  occurrence/timezone/capacity validation, room/track/staff overlap detection,
  and owner-level audited override.
- Added one responsive shared schedule component and a class-based Livewire
  create/edit surface in the existing event workspace. Public viewers do not
  receive drafts or private staff assignments.
- Added direct Action, policy, schema, encryption, conflict, Livewire,
  localization, factory, and seeder tests in
  `EventScheduleWorkflowTest` and `EventLifecycleFoundationTest`.
- Passed 2,362 sequential PHP tests with 78,760 assertions, full
  Pint/Larastan, isolated fresh migration plus repeat seed, production build
  and audits, and six event desktop/mobile browser audits with zero console
  errors.

Session attendee reservations/waitlists, schedule-change notifications,
venue entities beyond event-scoped rooms, and keyboard reordering remain open
and are not claimed by this delivery.

## Current Planning: Portal And Events Completion

Status: `in progress` on 2026-08-20; P02 organization authority and P03
place/location/venue authority have verified foundations, while every parent
package and the remaining portal/event scope stay open

The live audit found 3,449 `portal.*` requirements still without Point 12
evidence and 4,883 of 4,968 `event.*` requirements still planned/discovered.
The current code is not empty: it has one authenticated shell, 162 first-party
routes, the canonical event aggregate, lifecycle/occurrence foundations,
multi-pet registration, manual attendance, and occurrence-scoped schedules.
The gap is therefore a combination of missing advanced domains and existing
portal behavior that has not yet been reconciled against atomic requirements.

The factual audit is
`docs/audits/portal-events-completion-gap-analysis.md`. The unbounded,
dependency-ordered implementation contract is
`docs/plans/portal-events-completion-master-plan.md`. Its 36 packages begin
with evidence reconciliation, organization and location authority, then add
portal contexts/search/calendar/dashboard infrastructure before completing
event builder, eligibility, registration, capacity, payment, check-in, safety,
specialized event domains, UI migration, seeding, verification, and release.

## Current Delivery: P02 Organization Authority Foundation

Status: `implemented, verified, and published foundation` on 2026-08-03

The first P02 slice adds the canonical organization tenant, nine independent
membership roles, account-bound expiring invitations, operational
restrictions, suspension, append-only audit, guarded demo seeds, localized
class-based Livewire workspaces, and responsible-organization event authority.
Wrong/former tenant access fails in queries and Policies, ordinary members do
not receive email or restriction reasons, and invitation tokens remain hashed
in records and absent from public Livewire state.

This does not close P02. Organization locations, selected context switching,
verification review/renewal, notification delivery and revocation, downstream
finance/marketplace/shelter operations, authoritative backfill, and exact
portal/event evidence remain open. Scope and current gate evidence are in
`docs/plans/portal-organization-authority-foundation-work-package.md`.

## Current Delivery: Global Page Identity Standardization

Status: `in progress`; directory, workflow, event, and forum presentation waves
verified on 2026-08-03

The complete route classification, canonical `x-page-header` contract,
thirteen priority regression routes, forum category/subcategory information
architecture, meetup upgrade gate, global migration waves, and final quality
gates are recorded in
`docs/plans/global-page-identity-standardization-plan.md`.

The first slice extended `x-page-header` with a stable accessible heading,
metadata, and action regions. It migrated medical records, care journals,
lost-and-found, marketplace, experts, and messages while retaining the
existing reference-directory consumers. The nine message folders remain above
the messaging shell, and the global linked-media navigation remains unchanged.

The current messaging continuation also replaces locale-dependent call state
with stable codes, moves call control/icon preparation into a class-based Blade
component, localizes the complete preflight surface in EN/LT/RU, and makes the
existing conversation-details route display its context panel at mobile and
tablet widths with a localized return control. This reuses the same protected
route, presenter, and context projection and adds no query or authorization
path.

The next detail-page audit closes the `/share/{target}` English-fallback
surface with a dedicated 42-leaf EN/LT/RU contract. `SharePresenter` resolves
five stable target families and three stable delivery channels, prepares
recipient actions and canonical icon names, and leaves destination, access,
and mutation boundaries unchanged. The measured presenter query delta is zero.

The following deliberate-profile audit closes `/neighbors/ari-jensen`. The
neighbors domain grows from 71 to 160 exact-parity leaves, and
`NeighborProfilePresenter` now prepares the profile copy, statistics,
follow/message/walk action payloads, pet routine, mutuals, communities, and
canonical icon names. The profile-led hero remains intentional, all existing
destinations and authenticated mutation boundaries remain unchanged, and the
presenter query delta is zero.

The next Package 8 wave migrated the medical-record, care-journal,
lost-and-found, marketplace, and expert create/edit/booking flows, the device
directory and connect flow, and both professional-workspace states. Their ten
route-ledger entries now enforce the canonical page identity, and the retired
device directory selector was removed.

The same wave then standardized the canonical database content feed, social
preview feed, all prepared composer modes, the knowledge directory, and all
three knowledge editor modes. This retires page-level feed headings and
knowledge uses of the generic forum header without changing their filters,
forms, authorization, or Livewire state.

The event directory and database-backed event workspace now share the same
page identity while preserving the event image, status, organizer context,
privacy policy, lifecycle, and registration controls. Created prototype event
details remain classified under the deliberate detail-hero contract.

The forum directory, topic editor/detail, persistent groups, journals,
mentorship, expert sessions, and administration now use the canonical header.
The directory also exposes all roots while rendering only the active root's
direct children; child selection is validated and filtered server-side through
an indexed Eloquent scope.

The category tree is now a dedicated anonymous Blade component in the main
discussion column instead of a narrow left sidebar. Its progressive root
catalogue, selected-category purpose, breadcrumbs, and complete child grid
retain the existing query-string contract and zero-query warm tree cache. The
desktop directory keeps only the contextual knowledge/update rail; tablet and
mobile layouts move that rail below the topic stream without horizontal page
overflow. Display labels use Unicode-safe sentence capitalization while the
source manifest, stable keys, aliases, and slugs remain unchanged.

Shared actions and filter chips now retain the 44-pixel touch target at desktop
as well as mobile widths. The Blade localization audit also understands bound
component props, preventing already-localized values from being rewritten and
double escaped.

The complete isolated Pest run passed 2,484 tests and 80,398 assertions. The
275-test affected-domain run passed 3,488 assertions; Pint, Larastan,
localization, Vite, dependency audits, cache smoke, migration lifecycle,
isolated migration/seed/idempotency, forum-source preservation, and diff checks
also passed. Authenticated browser checks covered 16 route families at 375 px,
the forum at 1,440 px, a selected subcategory, `/meetups`, and an event
workspace with one canonical header, one `h1`, no horizontal page overflow,
44-pixel actions, no console errors, and no SQL error. The live classification
ledger covers all 111 current first-party GET routes. The stable requirement
ID, remaining locale/zoom/forced-colors fixtures, deliberate detail/workspace
exception audit, scoped publication, and final global audit remain open; this
delivery is not globally complete.

## Completed Delivery: Guest Join Page

Status: `superseded` by the authenticated portal boundary on 2026-08-03

- Replaced the guest root prototype feed with the localized, privacy-aware
  joining experience specified in `docs/plans/join-landing-page-plan.md`.
- Preserved the stable `home` route name; active verified members enter the
  canonical content feed and active unverified members to email verification.
- Used one primary account-creation action, passive Blade, first-party product
  presentation, current design tokens, and no guest database query.
- Removed fictional member identity and private member navigation from the
  guest document.
- Verified route state, auth continuity, EN/LT/RU, metadata, accessibility,
  320-1920 pixel browser behavior, Pint, Larastan, 2,037 serial Pest tests,
  and the production Vite build. The still-tested prototype feed was retained
  behind the authenticated `preview.feed` route instead of being deleted.

The market and settings rationale is recorded in
`docs/audits/pet-social-network-benchmark.md`. A consolidated settings center
is a separate future work package and must be mapped to exact open requirement
IDs before implementation.

## Current Delivery: Authenticated Portal Boundary

Status: `verified` on 2026-08-03

- Added one central session-aware boundary before route-model binding and made
  it persistent across Livewire updates.
- Reduced anonymous access to localized login, registration, and password
  recovery; JSON product requests return `401` without product data.
- Restricted product access to active verified accounts while retaining
  route-specific policies, grants, throttles, and step-up checks.
- Revoked anonymous medical/care/device token-share access as an outer route
  boundary without weakening token expiry, scope, revocation, or audit rules.
- Disabled direct local storage serving and public storage-link generation.
  Product uploads render through a canonically contained authenticated media
  route with bounded content types.
- Verified 2,092 serial Pest tests and 73,983 assertions, including guest
  zero-query denial, route ordering, Livewire upload/preview denial, token
  shares, traversal, unsupported content, and symbolic-link escape.
- Verified the complete non-test release gate: dependency audits, production
  build, fresh migration and repeated seed, cache smoke checks, generated
  requirement checks, and authenticated browser flows with the EN/LT/RU
  account-entry shell at 320-1920 pixels and no console errors.

The exhaustive execution and release plan is
`docs/plans/authenticated-portal-access-plan.md`.

## Current Delivery: Forum Database Correctness Reconciliation

Status: `verified` on 2026-08-03

- Added portable fixed-value constraints and backed enum casts for answer
  votes and photo reactions.
- Added moderation-case optimistic versioning and unique closure request keys.
- Added one policy-authorized, row-locked, retry-bounded, idempotent close
  Action with transactional bulk audit events and no new route or UI.
- Corrected the canonical topic answer pointer during competing single-answer
  acceptance.
- Added direct database, rollback/reapply, authorization, replay, stale-write,
  archival/cast, duplicate-attempt, and constant-query-growth tests.
- Verified the complete combined gate: 2,303 tests and 76,179 assertions,
  117 fresh migrations / 196 tables / stable repeat seed, full Pint/Larastan,
  dependency audits, Vite/cache compilation, deterministic 38,377-record
  generation, and EN/LT/RU loopback Chrome checks.

Exact scope, evidence, remaining gates, and stop conditions are in
`docs/plans/forum-database-correctness-reconciliation-work-package.md`.

## Current Delivery: Global Linked Media Navigation

Status: `verified` on 2026-08-03

- Added one passive Blade primitive that links representative media only from
  an explicit server-prepared canonical target and remains passive when the
  target is absent.
- Migrated eligible pet, group, neighbor, meetup, discovery, profile, expert,
  booking, messaging, and marketplace projections without adding queries or
  guessing routes.
- Preserved viewer, gallery, current-page, QR, map, video, upload, action, and
  private-download semantics through an exhaustive 73-template inventory.
- Added EN/LT/RU accessible labels, visible focus, reduced-motion and
  forced-colors behavior, exact-destination tests, nested-interactive source
  guards, and responsive browser checks.
- Passed the 19-test contract, 67-test affected slice, Pint, Larastan,
  dependency audits, Vite build, cache smoke checks, fresh isolated migration,
  repeat seed, and 24-route/viewport browser matrix.
- The final serial repository suite passed 2,303 tests and 76,111 assertions in
  131.130 seconds after an earlier concurrent loader conflict disappeared.

The exhaustive scope, classifications, acceptance criteria, and gate evidence
are recorded in `docs/plans/global-linked-media-navigation-plan.md`.

## Current Delivery: Canonical Places And Venues

Status: `verified` on 2026-08-03

- Replaced the static place identity boundary with policy-scoped Eloquent
  places, venue areas, exact-location grants and audits, and dynamic detail
  routes for newly submitted places.
- Retained the complete server-rendered directory, map alternative, emergency
  clinic mode, EN/LT/RU content, and encrypted per-user saves, follows, visits,
  private check-ins, corrections, warnings, reviews, and questions.
- Added reversible indexed migrations, production Actions, privacy-safe public
  projections, idempotent authority/catalog seeders, explicit factories, and
  event-to-place/venue links.
- Verified 13 directory tests with 140 assertions, 20 authority tests with 153
  assertions, scoped Pint and Larastan, 126 fresh migrations across 211 tables,
  repeated seed stability, dependency audits, Vite/cache compilation, and the
  final serial suite of 2,579 tests with 81,626 assertions.
- Verified authenticated desktop/mobile browser flows for `/places` and
  `/places/vingis-quiet-loop` with no overflow, broken images, raw translation
  keys, unnamed controls, console errors, or protected-address disclosure.
  The browser gate also caps place-card height at 480px on desktop and 720px on
  mobile; the final measured ranges were 384-473px and 614-654px respectively.

The authority, privacy, lifecycle, schema, and acceptance decisions are
recorded in
`docs/plans/portal-place-location-venue-authority-work-package.md`.

## Current Delivery: Canonical Portal Discovery

Status: `release verified` on 2026-08-03; attributable publication prepared.

- Replaced the static four-card discovery demonstration, fictional Richmond
  query, local pulse, trending topics, and weekend promotion with one bounded
  database-backed recommendation hub.
- Reused current event, group, place, expert, pet, user-actor, publication,
  social-block, localization, media, status, action, shell, and deep-link architecture.
- Added strict query/category validation, account and actor block filtering,
  `is_recommendable` filtering, public-only projections, and explicit omission
  of exact event/place locations.
- Added user-owned, policy-scoped, idempotent item/category hide and reset
  preferences with reversible indexed schema and a factory.
- Added canonical discovery directions, toolbar, sections, cards, empty/hidden
  states, EN/LT/RU translations, constant-query tests, and a repeatable browser
  gate for 1440/375/320px including long Lithuanian content.
- Added active verified member recommendations, a stable policy- and
  block-scoped `members.show` profile, and visible post recommendations through
  `ContentPublication::visibleTo()` and the canonical content route.
- Targeted evidence passes 12 feature tests / 121 assertions and the linked-media
  discovery contract. The final serial suite passes 2,657 tests / 84,589
  assertions; fresh migration plus repeat seeding, Larastan, Pint, dependency
  audits, production Vite, and the three-viewport browser gate also pass with
  no overflow, broken media, private-location leak, unnamed control, raw key,
  or console error. The all-category service projection is 12 bounded queries
  for 16 recommendations across all seven sections in the current demo world.

The baseline audit, architecture decisions, complete delivery plan, and release
evidence are in `docs/plans/discover-modernization-plan.md`; the stable page
contract is `docs/portal/discovery.md`.

## Current Delivery: Canonical Pet Workspace

Status: `implemented and release verified` on 2026-08-03.

- Replace `/pets` static nearby-pet fixtures and session-only Follow controls
  with one policy-aware Eloquent workspace for owned and actively shared
  `PetProfile` records.
- Keep cross-user pet recommendations under `/discover?category=pets`, expose
  pending manager invitations separately, and reuse canonical creation,
  profile, care, health, media, status, action, and pagination contracts.
- Validate query/filter/sort URL state, paginate at twelve, eagerly load the
  current manager and primary protected media, and keep query growth constant.
- Provide purpose-specific empty/filtered-empty states and full EN/LT/RU copy.
- Verify desktop and mobile behavior through the repeatable loopback Chrome
  gate in `scripts/pet-workspace-browser-check.mjs`.
- Release evidence includes the 2,670-test/84,934-assertion serial suite,
  zero-error Larastan, dependency audits, production Vite build, isolated fresh
  migration/seed and repeat-seed checks, and the three-viewport browser gate.

The baseline, decisions, implementation passes, security boundaries, and gate
evidence are recorded in `docs/plans/pet-workspace-modernization-plan.md`.
