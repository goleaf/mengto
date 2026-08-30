# Portal Point 12 Completion Work Ledger

Date: 2026-08-30

Status: active

Canonical package: `POR-01` through `POR-12` in
`docs/implementation-plan.md`, implementing `PLA-P01`, `PLA-P04` through
`PLA-P11`, and the required `PLA-P33` through `PLA-P35` evidence/release work.

## Shared-tree boundary

The repository was clean at `ae4ac3241f99b05645dcc07316f424dfb877892e`
when this task began. Concurrent Places, events, seeding, performance, and
catalogue-coordination changes appeared afterward. Those files and hunks are
unrelated unless the principal records an explicit transfer here. Specialists
must not edit, stage, commit, reset, discard, or reformat any file. The
principal alone integrates changes and later uses a temporary Git index for
the attributable Portal slice.

## Specialist protocol

Each discovery specialist receives one exclusive scope and returns:

1. Current authorities and reusable paths.
2. Exact gaps against Point 12 and applicable `PLA-*` packages.
3. Privacy/authorization/query/payload risks.
4. Proposed red tests with exact file targets.
5. Proposed minimal implementation paths and dependencies.
6. Atomic requirement IDs that may become evidence candidates only after the
   proposed direct checks pass.
7. Explicit non-goals and conflicts with another specialist scope.

Discovery is read-only. Later implementation assignments remain exclusive and
test-first. Reviewers are independent from implementers and review a frozen
attributable diff.

| Specialist ID | Exclusive scope | Read-only paths | Required deliverable | Status/disposition |
| --- | --- | --- | --- | --- |
| PS-PUBLIC | Guest allowlist, public owner/pet/organization/place/event/result/archive projections, canonical URLs and compatibility redirects | Routes, portal middleware, public controllers/presenters/views, relevant models/policies/tests and Point 12 public/SEO atoms | Projection field allowlists, route/redirect matrix, enumeration/privacy red tests, query ceilings, reusable authority map | assigned for discovery |
| PS-CONTEXT | Selected pet and organization context, request/hydration reauthorization | Session/context middleware, user-pet authority, organization memberships, Livewire lifecycle, relevant policies/tests | Context state machine, invalidation and deep-link rules, HTTP/Livewire red tests, minimal integration boundary | assigned for discovery |
| PS-NAV | Shared navigation registry, breadcrumbs, active module, contextual actions, safe back destinations | Shell/header/mobile navigation/components, route/page matrices, localization/tests | Registry/page descriptor contract, capability rules, route coverage and keyboard/browser test design | assigned for discovery |
| PS-NOTIFY | Notification center, categories/preferences, safe previews and canonical deep links | Existing notification/domain models and Actions, controllers/views, policies, translations/tests | Reuse map, recipient/deep-link resolver contract, privacy/dedupe/query red tests | queued |
| PS-SEARCH | Privacy-safe global search and existing Discovery integration | Domain models/scopes, `DiscoveryCatalog`, existing search-case domain, requests/controllers/views/tests | Provider contract, scope-before-count rules, canonical result types, privacy/query/browser red tests | queued |
| PS-DASH | Role-scoped dashboards and organization/professional/organizer/moderator workspaces | Existing dashboards/workspaces, memberships/roles/policies, aggregate queries/tests | Widget registry/capability matrix, urgent-state ordering, isolation/query red tests | queued |
| PS-SETTINGS | Complete settings and data-control interfaces | Profile settings, privacy/social/notification/session/export/deletion authorities and policies | Settings IA, authoritative mutation reuse, unsupported-provider states, Livewire/security/payload tests | queued |
| PS-QUICK | Command palette and quick actions | Shell, route/page/navigation registry proposals, existing Actions/routes/policies, JS/CSS/tests | Typed registry, safe search/authorization rules, keyboard/focus model, mutation/direct-call red tests | queued |
| PS-PRIVACY | Cross-cutting privacy and authorization review | Frozen attributable Portal diff plus generated projections/payloads/routes/caches | Independent leak/enumeration/IDOR/timing/cache/serialization report with reproducible evidence | queued; final review only |
| PS-A11Y | Accessibility, responsive states, localization, query and Livewire payload evidence | Frozen attributable UI/test diff, EN/LT/RU files, CSS/JS/browser scripts | Independent WCAG 2.2/keyboard/mobile/zoom/forced-colors/reduced-motion/state/parity report | queued; final review only |
| PS-FINAL | Final architecture, regression, evidence, and publication review | Frozen attributable diff, complete gate outputs, exact evidence overlay and generated outputs | Requirement-by-requirement disposition, false-promotion check, unresolved-risk report, publish/no-publish recommendation | queued; final review only |

## Principal dispositions

No specialist finding is accepted merely by receipt. The principal reproduces
material findings, records accepted/rejected/deferred disposition here,
integrates only accepted in-scope changes, and reruns affected checks. Atomic
evidence promotion remains blocked until `POR-12` direct verification.

| Finding | Disposition | Evidence |
| --- | --- | --- |
| Pending specialist discovery | pending | none |
