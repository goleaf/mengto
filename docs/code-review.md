# Code Review

## Repository Audit Review — 2026-08-30

Three independent read-only reviewers inspected the repository inventory,
foundational diff, adjacent authorization/data boundaries, tests, generators,
factories, seeders, commands, and documentation. Codex `/review` was not
available in this environment. The work ledger is
`docs/audits/repository-audit-work-ledger.md` and the complete generated audit
surface is `docs/audits/repository-inventory.md`.

| Finding | Severity | Disposition and evidence |
| --- | --- | --- |
| Aggregate-only audit omitted exact paths and call chains | High | Accepted: deterministic route/role/table/symbol/file/Markdown inventory and seven exact workflow chains generated and byte-tested |
| SQLite driver was assumed but not declared | Medium | Accepted: `ext-pdo_sqlite` is a direct development requirement and architecture contract |
| Global Gate and grant acceptance lacked order/search/device and different-bearer coverage | Medium | Accepted: real Gate denial tests cover all named private domains; bound mismatch and unbound different-bearer tests cover care, medical, and devices |
| Denied medical/care downloads consumed grants and logged success | High | Accepted: downstream checks execute inside the locked transaction; denial regressions assert zero views/audits |
| Shared care entry released the grant lock before the write | High | Accepted: permission checks and `CreateCareEntry` run inside `ResolveCareAccess::execute`; architecture and behavioral regressions enforce it |
| Marketplace demo seeder could be invoked directly in production | High | Accepted: independent fail-closed environment guard and no-mutation test |
| Decimal inputs and totals could lose exactness or exceed schema width | Medium | Accepted: pre-validation canonicalization, checked minor-unit value object, exact boundary/rollback/call-site tests, and finite legacy JSON normalization |
| Stateful browser scripts could mutate an arbitrary loopback database | High | Accepted: npm scripts use a temporary SQLite/ephemeral-loopback wrapper; direct Node runners refuse without consent; behavioral isolation proof removes temporary paths |
| Direct Pest after `config:cache` could inherit a non-test database | Critical | Accepted: canonical `scripts/run-tests.php` clears cached configuration before Pest; Composer retains the same ordered behavior |
| External forum history check masked deterministic catalogue assertions | Medium | Accepted: external preservation and deterministic 38,377-record checks are separate tests |
| Generated evidence, formatting, and documentation drifted during review | High | Accepted: evidence regenerated after the frozen correction slice, Pint applied, and canonical audit/plan/testing/review documents synchronized |
| Child disclosure boundaries need broader redesign | Medium | Deferred to prompts 09/10 with explicit plan ownership; this pass did not change the existing product boundary |
| Grant `actor_role` names the scoped grant role | Suspected | Rejected as an identity defect: authorization and attribution use the authenticated `actor_key`; the role is purpose metadata and cannot grant capability |

The repository correctness reviewer scored the pre-correction audit at 80%; its
required exact-inventory and provenance corrections are now implemented. The
security reviewer confirmed token hashing, private-file containment, and the
removal of the global administrator bypass after its accepted transactional,
seeder, money, and coverage fixes. The regression reviewer confirmed the
focused correction slices and required a new final serial suite; final command
evidence is recorded in `docs/current-state-audit.md` and the canonical plan.

## Historical Review Checkpoint

The sections below preserve the earlier modernization review record and counts
as historical evidence. They are not the current repository-audit verdict.

## Review Order

1. Security and data integrity.
2. Behavioural compatibility.
3. Authorization and validation.
4. Migration and rollback safety.
5. Query/performance regressions.
6. Localization and accessibility.
7. Tests and observability.
8. Maintainability and documentation.

## Historical Final Review Findings

No unresolved repository-fixable critical, high, medium, or low-severity
defect was found in the modernized application boundaries.

External review boundaries, ordered by impact:

1. **External: physical device execution is unavailable.** Platform lifecycle,
   privacy, authorization, idempotency, retention, offline, and fail-closed
   behaviour pass, but selected hardware and vendor credentials are required
   for the nine blocked device requirements.
2. **Environmental: automated coverage percentage is unavailable.** The full suite
   passes, but the PHP runtime lacks PCOV/Xdebug. See `TEST-COVERAGE-001`.
3. **Editorial: localization is structurally complete.**
   Lithuanian and Russian preserve keys/placeholders and safely fall back to
   English where no approved native wording exists; native review remains
   external.
4. **Measured boundary: the retained semantic SCSS bundle is 249.90 kB
   uncompressed.** It is
   measured, cacheable, and stable; a component-by-component reduction needs
   visual regression evidence rather than a wholesale rewrite.

## Resolved Findings

- fixed prototype actor at protected boundaries;
- missing auth middleware and user-aware policies;
- guest access to private medical/care/device data;
- absent Livewire/localization/static-analysis architecture;
- non-idempotent seeding;
- uncovered foreign-key indexes;
- dynamic/static untranslated Blade messages;
- browser-session-authoritative social mutations;
- missing durable pet social identities and offline care synchronization;
- incomplete device lifecycle, retention, event grouping, and stolen-device
  policy;
- 320 px booking-page horizontal overflow;
- meaningless framework example tests.

## Verification Evidence

- serial Pest: 1,384 tests, 50,006 assertions, all passed;
- parallel Pest: not rerun for this checkpoint because the repository uses a
  shared SQLite test boundary and serial evidence is authoritative;
- Larastan/PHPStan level 5: zero errors;
- Vite production build and Blade/config/route caches: passed;
- Composer and NPM audits: no advisories/vulnerabilities;
- temporary SQLite fresh migrate/seed and repeat seed: passed with 93
  migrations and 146 tables;
- browser auth/private/mobile/desktop review: no final console errors or
  document overflow at audited widths.

Before publication the complete and temporary-index staged diffs are inspected
for unrelated files, secrets, generated local artifacts, and debug code.
The pre-existing untracked `.agents/vendor/` tree remains outside the commit.
