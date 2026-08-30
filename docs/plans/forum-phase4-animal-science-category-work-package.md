# Phase 4 Animal-Science Category Work Package

Date: 2026-08-30

Status: implemented and targeted verification complete; final verification pending

## Goal And Selection

The initiating phase placeholder was literal. The canonical next-pass record
in `docs/plans/forum-current-progress.md` selects the exact category-25 source
section, “animal science, research, and evidence,” as the next dependency-safe
Phase 4 package. This package owns exactly 58 currently open Phase 4 atoms,
`forum.category.0237` through `forum.category.0294`.

The same immutable source section also contains “taxonomy and systematics”
(`animal.taxonomy.0021`, Phase 5) and “case reports”
(`forum.moderation.0012`, Phase 7). They are explicit dependencies outside
this package and remain open unless their own later packages independently
prove them.

## Exact Requirement Inventory

| ID | Required behavior |
| --- | --- |
| `forum.category.0237` | Preserve the category-25 source section as an independently traceable scope. |
| `forum.category.0238` | Preserve the stable-key declaration. |
| `forum.category.0239` | Implement and verify `forum.animal-science-evidence`. |
| `forum.category.0240` | Preserve the purpose declaration. |
| `forum.category.0241` | Implement the evidence-oriented purpose for responsible claim and research discussion. |
| `forum.category.0242` | Preserve the ordered subcategory declaration. |
| `forum.category.0243` | General animal science. |
| `forum.category.0244` | Anatomy. |
| `forum.category.0245` | Physiology. |
| `forum.category.0246` | Genetics. |
| `forum.category.0247` | Epigenetics. |
| `forum.category.0248` | Evolution. |
| `forum.category.0249` | Domestication. |
| `forum.category.0250` | Cognition. |
| `forum.category.0251` | Emotions. |
| `forum.category.0252` | Learning science. |
| `forum.category.0253` | Behavior research. |
| `forum.category.0254` | Welfare science. |
| `forum.category.0255` | Veterinary research. |
| `forum.category.0256` | Nutrition research. |
| `forum.category.0257` | Reproduction research. |
| `forum.category.0258` | Aging research. |
| `forum.category.0259` | Pain research. |
| `forum.category.0260` | Rehabilitation research. |
| `forum.category.0261` | Environmental enrichment research. |
| `forum.category.0262` | Comparative medicine. |
| `forum.category.0263` | Conservation science. |
| `forum.category.0264` | Ecology. |
| `forum.category.0265` | Population science. |
| `forum.category.0266` | Epidemiology. |
| `forum.category.0267` | Research methods. |
| `forum.category.0268` | Study design. |
| `forum.category.0269` | Statistics. |
| `forum.category.0270` | Interpreting risk. |
| `forum.category.0271` | Correlation and causation. |
| `forum.category.0272` | Systematic reviews. |
| `forum.category.0273` | Meta-analyses. |
| `forum.category.0274` | Clinical trials. |
| `forum.category.0275` | Observational studies. |
| `forum.category.0276` | Laboratory studies. |
| `forum.category.0277` | Preprints. |
| `forum.category.0278` | Peer review. |
| `forum.category.0279` | Replication. |
| `forum.category.0280` | Conflicts of interest. |
| `forum.category.0281` | Funding disclosures. |
| `forum.category.0282` | Product claims. |
| `forum.category.0283` | Advertising claim analysis. |
| `forum.category.0284` | Source verification. |
| `forum.category.0285` | Evidence grading. |
| `forum.category.0286` | Myth checking. |
| `forum.category.0287` | Outdated recommendations. |
| `forum.category.0288` | Research summaries. |
| `forum.category.0289` | Plain-language science explanations. |
| `forum.category.0290` | Research requests. |
| `forum.category.0291` | Citizen science. |
| `forum.category.0292` | Ethical research discussions. |
| `forum.category.0293` | Open data. |
| `forum.category.0294` | Research corrections and retractions. |

## Reconciliation And Architecture Contract

The immutable manifest already appears to contain the category-25 root,
purpose, and all 54 source children. Discovery must reconcile that apparent
implementation against the source, production synchronizer, persisted rows,
localized projection, cache lifecycle, UI, factories, seeders, and tests.
File presence is not completion evidence.

Reuse the canonical category manifest, checksum validator, synchronization
Action, Eloquent aggregate, locale-row model, bounded cached category tree,
class-based Livewire components, passive Blade, and Laravel language files.
Do not add a parallel hierarchy, modify historical migrations, create a
category-specific authorization rule, or mark the Phase 5/7 atoms complete.

## Reconciliation Result

Three independent read-only reviews confirmed that the immutable manifest,
checksum validator, transactional synchronizer, normalized category and
translation rows, reviewed root translations, locale-scoped cached tree,
request validation, server-prepared directory projection, factories, and
idempotent system seed already implement the selected hierarchy. The package
therefore retains those boundaries and adds exact characterization rather
than speculative schema, Policy, Action, Service, or translation machinery.

The review found one presentation defect directly exercised by the selected
category: the shared category navigator used Georgia and viewport-scaled
`clamp()` typography for its category headings. That violated the canonical
Instrument Sans fixed-rem scale. A focused contract observed that defect in
RED, and the smallest correction restores the inherited product font and the
fixed `1.125rem` title scale. Unlocalized shared filter/sort labels, generic
visibility projection, and an unbounded administrator-defined category count
are broader existing debts; this package does not falsely close them.

## Acceptance Criteria

1. A focused Pest contract pins number `25`, exact stable key and slug,
   source name, purpose, and all 54 children in immutable source order.
2. The production synchronizer persists the root and all children with exact
   stable keys, slugs, positions, parentage, active/system flags, and reviewed
   EN/LT/RU root name/purpose rows while preserving administrator categories.
3. The public category projection renders the selected root purpose and all
   54 child destinations through server-validated selection and the existing
   eager-loaded tree; fallback mode has the same semantics and no user-facing
   raw keys.
4. Category reads retain the existing query/cache budget, locale-scoped cache
   key and invalidation behavior, safe empty/error states, stable links,
   semantic headings, keyboard path, visible focus, 44-pixel mobile targets,
   reduced-motion/forced-colors behavior, safe empty/manifest-fallback states,
   and no horizontal overflow.
5. No schema change is introduced unless a failing test proves a normalized
   persistence gap. Any additive migration must preserve populated data and
   pass rollback/reapply. No route, controller, policy, Form Request, Action,
   Service, factory, seeder, translation, or UI change is made merely to create
   evidence when the existing boundary is correct.
6. Every behavior repair follows red-green-refactor. Targeted tests pass
   before evidence changes.
7. The evidence overlay records exactly the 58 listed IDs as implemented and
   tested but keeps their final result in progress until every required
   repository gate passes. Generator output, phase counts, traceability,
   compliance, progress, implementation plan, and changelog agree with that
   conservative result; the two later-phase atoms stay open.
8. An independent reviewer examines a frozen attributable diff and every
   material finding is dispositioned and retested before publication.

## Verification Sequence

1. Run the exact `jq` inventory for open Phase 4 records in the category-25
   source section and confirm 58 IDs.
2. Run the focused category-25 test. Retain already-correct source and
   persistence behavior, observe the navigator typography contract in RED,
   implement the smallest correction, then rerun the full contract green.
3. Run related category synchronization, localization/cache, schema,
   architecture, authorization, factory, and seeder tests.
4. Run the immutable source check and the deterministic forum requirement
   generator check with the documented memory limit.
5. Run Pint, Larastan, full sequential Pest, coverage, isolated fresh/repeat
   seed and rollback/reapply, Composer validation/audit/platform checks, npm
   audit/build, cache smoke checks, and applicable desktop/mobile browser
   checks. Preserve exact external blockers rather than converting them into
   passes.
6. Freeze and independently review the attributable diff, run
   `git diff --check`, inspect the temporary-index staged diff, commit the
   coherent package on `main`, and push only after required gates pass.

## Observed Verification

- Focused RED isolated the category heading's Georgia and viewport-scaled
  typography while five source/persistence/UI tests remained green. The
  browser-entry contract later failed before its bounded command existed.
- Focused GREEN passed 7 tests and 104 assertions.
- The category/cache/administration slice passed 62 tests and 6,097
  assertions. Localization, responsive, schema, and taxonomy-factory checks
  passed another 50 tests and 37,056 assertions.
- An earlier isolated Chrome package passed at 1440px English, 375px Lithuanian,
  320px English reflow, and 1024px Russian forced colors with exact localized
  copy, all 54 children, fixed typography, visible focus, 44px targets, zero
  overflow, zero raw keys, and zero console errors.
- Exact regeneration records only `forum.category.0237` through
  `forum.category.0294` as implemented, tested, and in progress. The resulting
  catalogue has 1,727 verified, 58 in-progress, and 36,592 discovered records;
  Phase 4 has 239 verified, 58 in-progress, and 1,166 discovered records.
- The immutable-source reconstruction remains externally blocked by missing
  Codex history entry `1785397895`. Coverage remains externally blocked because
  neither PCOV nor Xdebug is installed. A current bounded browser rerun now
  stops during isolated database seeding at `ReviewFactory.php:69` because
  concurrent unrelated factory work rejects an incoherent completed booking.
  The monolithic browser command also
  exceeded its 900-second wrapper limit in the current shared environment;
  an earlier bounded animal-science package passed, but neither browser gate
  is currently recorded as successful. Full Pest, Architecture, full Pint,
  and generated-inventory gates also failed on unrelated concurrent work, so
  final promotion is withheld.

## Rollback And Stop Conditions

Rollback is a normal revert of the coherent package commit. Evidence must be
reverted with any behavior it describes, then regenerated from the first-party
generator. Stop without promotion if the exact selected IDs drift, a source
checksum changes, any later-phase atom is promoted, a real privacy/security or
data-preservation issue appears, a required non-external gate fails, or an
unrelated dirty-tree path enters the attributable diff.
