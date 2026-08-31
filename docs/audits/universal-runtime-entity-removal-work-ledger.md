# Universal Runtime Entity Removal Work Ledger

Work package: `URE-001`  
Branch: `main`  
Starting HEAD: `d92cf5e74593a0a79b23c721ffc874fb76eba3d2`  
Starting origin/main: `d92cf5e74593a0a79b23c721ffc874fb76eba3d2`  
Principal: `/root` (all writes, cross-domain decisions, verification, staging, commit, and push)

## Shared-Tree Safety

The starting checkout contains extensive staged and unstaged work from the existing authenticated-identity delivery and unrelated unfinished EventCompetition, Place, portal, localization, and UI packages. No existing byte is assumed attributable solely from `git status`. The package will use path/hunk ownership review and a temporary `GIT_INDEX_FILE` only after all required gates are green.

## Read-Only Specialist Assignments

| Assignment | Exclusive audit scope | Required deliverable | Status |
| --- | --- | --- | --- |
| URE-A | Registration, verification, transaction, exact authentication | file:line data flow, defects, missing regressions | queued |
| URE-B | Onboarding, pet choice, zero side effects, stale Livewire | file:line lifecycle and mutation proof | queued |
| URE-C | Shell identity, AppShell, presenters, profile | explicit source-of-truth and caller inventory | queued |
| URE-D | Member/pet profile, privacy, policies, blocks, routing | IDOR/privacy matrix and route ownership | queued |
| URE-E | PrototypeState, PreviewService, preview/catalog/presenter graph | production caller graph and removal order | queued |
| URE-F | Routes and literal defaults/allowlists | route name/path/default/controller classification | queued |
| URE-G | Runtime persons, authors, contacts, hosts, attendees, experts | literal/semantic file:line inventory | queued |
| URE-H | Runtime pets, images, owners, breeds, relationships | literal/semantic file:line inventory | queued |
| URE-I | Messages, social, notifications | canonical model gaps and zero-data strategy | queued |
| URE-J | Meetups, groups, content, directories | canonical record/query gaps and migration order | queued |
| URE-K | Seeders, factories, environment guards, collisions, migrations | seed/core/demo classification and safety findings | queued |
| URE-L | EN/LT/RU entity data | key/file inventory and neutral/dynamic replacements | queued |
| URE-M | Sessions, cookies, local/session storage, Alpine, JS fixtures | legacy-state inventory and scoped cleanup contract | queued |
| URE-N | Security, privacy, caches, mass assignment, stale identity | adversarial findings with severity/evidence | queued |
| URE-O | Existing tests and permanent guardrails | old prototype contracts, missing RED cases | queued |
| URE-P | Frozen final diff | independent final compliance review | pending implementation |

Specialists are read-only: no file edits, no mutation-capable test runs, no Git state changes. The principal reproduces every material finding before disposition.

## Initial Inventory

| Finding | Classification | Disposition |
| --- | --- | --- |
| Runtime Ari/Mochi/Jamie/Theo/Bean copy across `lang/{en,lt,ru}/messages.php`, `ui.php`, `groups.php` | production runtime defect | remove entity keys after caller migration; replace system copy with neutral/dynamic values |
| Named attendee in `EventContentCatalog` | production runtime defect | replace with canonical event registration/user/pet projection |
| Named member in `GroupContentCatalog` | production runtime defect | replace with canonical group membership/user projection |
| Normal routes/controllers using `PrototypeState`/`PreviewService` | production runtime defect | characterize, add RED coverage, migrate per canonical domain, remove dead graph |
| Literal `/groups/apartment-pets-pdx`, route default, and group allowlist | production runtime defect | remove; use `ForumGroup:stable_key` binding and canonical policy |
| Known Mia/profile/Scout/Nori owner/profile routes and components | removed production dependency in current uncommitted identity slice | retain architecture regressions; verify no reintroduction |
| Model `Database\Factories\*Factory` imports for `HasFactory` | legitimate framework factory typing | explicitly allow; forbid runtime invocation/import outside models |
| `prototype.state.v1` in `SocialActorFoundationBackfill` | historical persisted-state compatibility, not yet cleared | inspect whether data is read as authority; retain only bounded migration/audit behavior |
| Historical `test@example.com` to `mia-carter` released migration mapping | immutable historical production defect | do not edit; design additive audit/repair and block publication until resolved |
| Named entities in seeders/factories/tests | potentially legitimate fixture | retain only when explicit, deterministic, guarded, and not a runtime dependency |

## Review Disposition Log

No specialist or final-review finding is accepted from summary alone. Each entry records evidence, reproduction, severity, owner, action, and rerun command before it is closed.

