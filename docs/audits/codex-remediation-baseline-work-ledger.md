# Codex Remediation Baseline Work Ledger

Audit date: 2026-08-30

Status: baseline discovery and verification in progress; product behavior is
out of scope.

## Protected Git Baseline

- Required branch: `main` only.
- Exact task-start commit: `153ae45c2bc6864ec6061dc407d82be68a437c26`.
- At task start, local `main`, the local `origin/main` tracking ref, and the
  remote `refs/heads/main` all resolved to that commit.
- The task began in a dirty shared tree. Every staged, unstaged, and untracked
  path that was present before this ledger, including paths added by concurrent
  work after the first snapshot, is treated as protected unrelated work.
- The principal agent is the only writer. Audit specialists are read-only.
- A task-owned commit may use a temporary `GIT_INDEX_FILE`; no existing index
  or working-tree content may be reset, discarded, or rewritten.

## Scope And Roles

| ID | Role | Read-only scope | Required output | Status |
| --- | --- | --- | --- | --- |
| CRB-A1 | Backend architecture auditor | PHP/runtime configuration, routes, controllers, Actions, Services, Form Requests, Policies, Livewire, Composer, and application boundaries | Evidence-backed findings, exact affected paths, gate implications, and remediation priorities | pending |
| CRB-A2 | Database and security auditor | Database configuration, migrations, models, factories, seeders, private data, authorization, token/file boundaries, and runtime ownership | Evidence-backed integrity/security findings, reproduction commands, and rollback risks | pending |
| CRB-A3 | Frontend, testing, and documentation auditor | Package metadata, Blade, JavaScript, CSS/SCSS, browser runners, Pest/static-analysis configuration, CI, deployment docs, and current plans | Evidence-backed frontend/test/docs findings and exact applicable gates | pending |
| CRB-R1 | Independent final reviewer | Frozen task-attributable diff plus observed gate record | Severity-ranked findings and release/push recommendation | pending |
| CRB-L1 | Principal agent | Instruction reconciliation, all commands that can mutate caches/files/databases, diagnosis, baseline-only fixes, documentation, Git integration, and publication decision | Complete verified baseline, remediation master plan, reconciled documents, final evidence, and safe publication decision | in progress |

## Execution Rules

1. Finish the complete applicable mandatory documentation order before
   changing code or product behavior.
2. Inventory every repository surface named in the task and derive commands
   from the repository rather than historical claims.
3. Run destructive database checks only through repository scripts that
   assert disposable SQLite paths.
4. Record every command exactly with its exit status and observed result.
5. Fix only defects that prevent truthful baseline execution or reporting.
6. Rerun every affected gate after a baseline fix; do not weaken a gate or
   hide a failure.
7. Reconcile the master plan, unfinished-work index, implementation plan,
   compliance matrix, and changelog without upgrading unverified status.
8. Commit and push to `origin/main` only if all applicable required gates pass
   and the independent final review has no unresolved material finding.

## Deliverables

- `docs/plans/codex-remediation-master-plan.md` with the exact baseline,
  failures, root-cause hypotheses, prioritized dependent work packages,
  acceptance criteria, automated evidence, external blockers, and rollback
  considerations.
- Reconciled references in the current unfinished-work index, implementation
  plan, compliance matrix, documentation index where applicable, and
  changelog.
- A final attributable diff and gate report that explicitly explains whether
  commit and push were permitted.
