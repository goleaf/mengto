# Portal And Events Completion Gap Analysis

Audit date: 2026-08-03

Status: factual baseline for completing Point 12 and Point 13

## Scope And Method

This audit compares the live repository with the normalized `portal.*` and
`event.*` requirements generated from Source Parts I and J. It inspects the
current route surface, Eloquent models, migrations, policies, Actions,
class-based Livewire components, Blade components, factories, seeders, tests,
portal documentation, event documentation, and requirement evidence.

Planning or historical documentation is not counted as implementation.
Existing code is not counted as verified Point 12 or Point 13 behavior unless
the generated requirement record has attributable evidence.

## Verified Baseline

- Branch: `main` at `8df3f0e`, synchronized with `origin/main` when the audit
  started.
- Working tree: clean when the audit started.
- Runtime: PHP 8.5.8, Laravel 13.23.0, Livewire 4.3.4, Tailwind CSS 4.3.3,
  Vite 8.2.0, Pest 4.7.5, and Larastan 3.10.0.
- Routes: 162 first-party routes after excluding vendor routes.
- Event routes: four GET entries under `/meetups`; state changes use the
  existing class-based Livewire components and Actions.
- Complete PHP checkpoint: 2,396 tests passed with 79,146 assertions in
  139.108 seconds.
- Point 12 catalogue: 3,449 `portal.*` requirements, all still
  `planned`/`discovered` in the generated evidence.
- Point 13 catalogue: 4,968 `event.*` requirements; 85 are
  `implemented`/`verified` and 4,883 remain `planned`/`discovered`.

The zero verified Point 12 total does not mean that the portal has no shell,
routes, navigation, settings, discovery, or notification UI. It means the
existing behavior has not yet been reconciled against and promoted through
the Point 12 evidence overlay.

## Post-Audit Progress

The dated baseline above remains historical. The organization authority
foundation was subsequently implemented on 2026-08-03: five organization
models/tables, current role-scoped membership, signed account-bound
invitations, eight independent restrictions, audit, three organization routes,
class-based Livewire workspaces, and responsible-organization event linkage.
The implementation and fresh verification are recorded in
`docs/plans/portal-organization-authority-foundation-work-package.md`.

## Existing Portal Foundation

The repository already has one authenticated Blade/Livewire shell, a
`PrimaryNavigation` registry, active-account and verified-email boundaries,
localized account settings, module directories, named routes, protected media,
and reusable page, status, form, action, empty-state, and responsive
components. Recent page-identity work classifies the first-party route set and
is migrating directories to the canonical page header.

The following Point 12 foundations are still absent or unproved:

1. Organization identity, membership, tenant Policies, role separation,
   suspension, audit, and event responsibility now exist. The active global
   organization context switcher remains open P05 scope.
2. No canonical selected-pet context exists across modules and quick actions.
3. No global search provider registry, privacy-scoped result contract, saved
   search, recommendation explanation, or sponsored-result model exists.
4. No command registry, command palette, or contextual quick-action registry
   exists.
5. No configurable role dashboard or persisted widget registry exists.
6. No aggregated calendar merges care, medical, bookings, events, and future
   volunteer shifts. No revocable external calendar feed exists.
7. The notification route exists, but Point 12 category, preference,
   aggregate unread count, deep-link registry, and cross-module evidence have
   not been completed.
8. Profile settings exist, but there is no complete settings architecture for
   account, privacy, notification, pet, organization, accessibility, locale,
   sessions, integrations, and data rights.
9. Breadcrumbs, pet switcher, organization switcher, and canonical contextual
   actions are not shared global components.
10. The public/private portal split is unresolved. The verified authenticated
    product boundary protects all product data, while Point 12 and Point 13
    require deliberately public directories and event metadata. Any public
    reopening needs explicit allowlisted presenters and privacy tests.
11. The route matrix has 162 entries, but the full stable page-identifier,
    audience, context, state, metadata, query-budget, and orphan-page contract
    is not yet evidenced for every route.
12. Module-specific dashboards, search-like directories, calendars, settings,
    and headers remain inconsistent enough that Point 12 cannot be marked
    complete.

## Existing Event Foundation

`ForumEvent` is the single canonical public event aggregate. The current event
domain has 16 modeled Eloquent entities and 17 dedicated tables including its
taxon pivot. It already provides:

- controlled event, format, visibility, lifecycle, registration,
  verification, pet-participation, accessibility, recurrence, team, session,
  and consent enums;
- authoritative owner separated from creator and organizer snapshot;
- scoped team membership and revocation;
- versions, series, concrete occurrences, occurrence overrides, and event
  history;
- encrypted exact location, online access, emergency, attendee-note, room,
  and schedule-conflict fields;
- basic participant registration, several managed pets, manual eligibility,
  event/occurrence capacity, waitlist promotion, manual check-in/check-out,
  invitations, updates, messages, reviews, reports, cancellation, and
  rescheduling;
- occurrence-scoped tracks, rooms, sessions, staff assignments, conflict
  detection, audited conflict override, and a responsive public agenda;
- bounded directory/workspace queries, EN/LT/RU catalogues, factories,
  production-safe backfill, environment-gated demo data, and browser checks.

## Event Gaps By Domain

| Domain | Atomic total | Verified | Primary gap |
| --- | ---: | ---: | --- |
| Foundation | 902 | 3 | complete ownership, welfare, public/private, and cross-module proof |
| Event types | 240 | 0 | full typed registry metadata and type-specific templates |
| Creation | 119 | 0 | draft wizard, save/resume, preview, templates, approval readiness |
| Lifecycle | 126 | 0 | approval records, scheduled opening, live/completion guards, corrections |
| Organization | 103 | 0 | organization tenant, invitations, suspensions, conflicts of interest |
| Venue | 114 | 0 | reusable venue/area entity, verification, expiring private access |
| Schedule/recurrence | 94 | 20 | recurrence generation, DST editing, reservations, waitlists, notifications |
| Eligibility | 104 | 0 | requirement registry, evidence, documents, audited exceptions |
| Registration | 160 | 0 | households, teams, guest pets, minors, roles, structured questions |
| Capacity/waitlist | 78 | 0 | separate capacity pools, checkout holds, fair occurrence/session offers |
| Tickets/payments | 153 | 0 | ticket, reservation, payment, refund, receipt, donation, payout state |
| Group walks | 96 | 0 | route geometry, groups, weather thresholds, lost-pet escalation |
| Training | 70 | 0 | method, level, prerequisite, lesson, equipment, completion aggregates |
| Exhibitions | 48 | 0 | exhibitor, display area, interaction rules, welfare removal |
| Competitions | 158 | 0 | categories, entries, judges, precise scoring, results, appeals, certificates |
| Conferences | 64 | 0 | speaker invitations, materials, scoped links, questions, recordings |
| Vendors/sponsors | 59 | 0 | applications, booths, contracts, disclosures, isolated fees |
| Volunteers | 65 | 0 | roles, qualifications, shifts, assignments, attendance, privacy |
| Communication | 68 | 0 | targeted announcements, delivery, acknowledgement, emergency broadcast |
| Check-in | 81 | 0 | signed QR, re-entry, operator/device audit, offline sync, corrections |
| Weather/cancellation | 71 | 0 | normalized weather plan and material-change consequences |
| Safety/incidents | 150 | 0 | safety plan, factual incidents, triage, stop scope, medical/lost-pet links |
| Media/privacy | 93 | 0 | versioned consent, minors, gallery moderation, exports, retention |
| Feedback/archive | 74 | 0 | typed private feedback, organizer report, resources, lost property |
| Notifications/calendar | 52 | 0 | category preferences, deep links, aggregated calendar and export |
| Discovery/SEO | 67 | 0 | public privacy-scoped search, explanations, fair exposure, metadata updates |
| Authorization | 67 | 4 | policies for every advanced aggregate and direct mutation |
| Validation | 64 | 5 | state-aware schemas, files, URLs, money, scores, dynamic questions |
| Livewire | 130 | 8 | builder, registration wizard, queue, check-in, scoring, announcements |
| Interface | 226 | 11 | canonical ticket, check-in, result, incident, cancellation, page states |
| Localization | 62 | 4 | all advanced workflows, formats, measurements, long-text evidence |
| Factory/seeding | 197 | 3 | factories for new models and the required 60 integrated demo scenarios |
| Testing/release | 459 | 16 | complete route, policy, concurrency, browser, query, and release gates |
| Performance | 74 | 3 | critical-page budgets, invalidation, high-volume check-in and results |
| Integration | 51 | 0 | organizations, payments, calendar, adoption, care, lost-pet, documents |
| Documentation | 83 | 0 | exact implementation mapping and synchronized final report |

Totals in this table group the generated domains without promoting any
unverified requirement.

## Important Partial Implementations

The following existing behavior is useful but cannot satisfy the complete
requirement by itself:

- Event price and currency are metadata only. Paid registration deliberately
  fails because there is no verified payment provider or event ledger.
- Capacity and waitlist work at the legacy event/occurrence level, but do not
  model human, pet, species, ticket, session, route-group, volunteer, vendor,
  accessibility, or room allocations independently.
- Manual check-in/check-out is server-confirmed, but no ticket, QR, re-entry,
  device, offline package, synchronization, or correction aggregate exists.
- Event reports reuse moderation, but a moderation report is not an event
  incident, safety plan, factual-source record, or urgent triage workflow.
- Generic reviews exist, but typed private feedback, accessibility/safety
  feedback, organizer reports, certificates, and post-event resources do not.
- Conference sessions exist, but speaker invitations, materials, participant
  session reservations, online link grants, captions, recordings, and
  moderated questions do not.
- Event team roles express intent for judges, safety, payments, vendors, and
  volunteers, but roles do not create the corresponding durable subdomains.
- Adoption, marketplace, medical, care, lost-and-found, credential, document,
  and notification modules exist, but most event-specific integration Actions
  and tests are absent.

## Documentation Defects

The canonical event documentation is mostly conservative, but two statements
must be corrected during the first implementation package:

1. `docs/events/index.md` still lists tracks/sessions as absent even though the
   schedule migration, models, Action, Livewire editor, tests, and evidence are
   published.
2. `docs/events/event-types.md` claims default icon, pet model, and public
   directory metadata that `ForumEventType` does not currently expose.

The route count and verification totals in dated evidence remain historical
facts and should not be rewritten unless the corresponding gate is rerun.

## Architectural Decisions Required Before Expansion

1. Build the canonical organization and membership aggregate before claiming
   organization-only visibility or cross-tenant event administration.
2. Reconcile static place presentation with a canonical persisted place/venue
   boundary before introducing event-only venue duplicates.
3. Keep payment eligibility separate from registration eligibility. Introduce
   the internal ledger and provider contract first; enable paid checkout only
   after one provider is selected, signed, audited, and configured.
4. Keep `ForumEvent` as the only event aggregate. Specialized models must be
   children or integrations, not parallel events.
5. Keep the authenticated product boundary. Public pages must be explicit,
   minimal projections rather than accidental guest access to internal
   workspaces.
6. Do not promote generated requirements in bulk. Each work package owns an
   exact requirement-ID manifest and evidence set.

## Completion Standard

Point 12 and Point 13 are complete only after every atomic requirement is
implemented, verified, blocked with a genuine external reason, or documented
as not applicable with evidence. A feature label, enum case, role name,
factory state, or plan is never sufficient proof. Each completed package must
include persistence where required, server-authoritative Actions, Policies,
validation, class-based Livewire or passive Blade UI, translations, factories,
seed scenarios, targeted tests, performance evidence, browser evidence where
applicable, synchronized documentation, exact staged-diff review, commit, and
push.
