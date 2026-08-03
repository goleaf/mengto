# Portal And Events Completion Master Plan

Plan date: 2026-08-03

Status: approved execution plan; implementation packages not started by this
document

## Objective

Complete the canonical portal architecture from Point 12 and the complete
event lifecycle from Point 13 without creating parallel users, pets,
organizations, locations, payments, messages, notifications, media, reports,
or translation systems.

This plan has no artificial package, file, test, or calendar limit. Work is
ordered by data authority and security dependencies. A later package may not
claim completion while an earlier dependency remains absent.

The factual starting point is
`docs/audits/portal-events-completion-gap-analysis.md`.

## Baseline

- 162 first-party routes.
- 3,449 `portal.*` requirements: 0 verified, 3,449 planned/discovered.
- 4,968 `event.*` requirements: 85 verified, 4,883 planned/discovered.
- Existing event domain: 16 Eloquent models and 17 event tables.
- Current complete checkpoint: 2,484 tests and 80,398 assertions.
- Current supported locales: EN, LT, and RU.
- Current branch workflow: direct, attributable packages on `main`.

## Non-Negotiable Invariants

1. `ForumEvent` remains the only canonical portal event aggregate.
2. Existing users, pet profiles, groups, credentials, medical records, care
   journals, adoption cases, marketplace records, lost-pet cases, reports,
   notifications, media, and documents are reused.
3. Browser and Livewire state is untrusted. Every mutation reloads, authorizes,
   validates, and invokes a server-owned Action.
4. Exact locations, online links, eligibility evidence, emergency contacts,
   minors, tickets, incidents, and unpublished results never enter public
   serialization or shared caches.
5. Eligibility, payment, attendance, competition, and incident decisions are
   independent state machines.
6. Money uses integer minor units. Scores use documented scaled integers or a
   deliberate decimal representation, never float.
7. Unknown data is never treated as verified, eligible, safe, accessible, or
   professionally approved.
8. Public product access is an explicit allowlist with minimal presenters. It
   must not weaken the authenticated portal boundary.
9. No requirement status is promoted without exact implementation and test
   evidence.
10. No package edits historical migrations. Every schema change is additive,
    reversible, indexed, and populated-data safe.
11. No Volt, Blade queries, Blade PHP blocks, raw SQL, dynamic Tailwind class
    construction, hardcoded user-facing text, or new isolated visual theme.
12. Every package preserves unrelated work and publishes only its attributable
    diff through a temporary Git index when necessary.

## Package Execution Protocol

Every package follows the same sequence:

1. Select an exact, non-overlapping requirement-ID manifest.
2. Re-read the affected models, migrations, policies, Livewire components,
   Blade components, translations, factories, seeders, tests, and docs.
3. Add failing tests for the smallest server-authoritative behavior.
4. Add one concern per migration and one meaningful operation per Action.
5. Implement Policies and validation before exposing mutation controls.
6. Add class-based Livewire or passive shared Blade UI only after the domain
   behavior passes.
7. Add all EN/LT/RU strings, factories, meaningful states, and integrated seed
   scenarios.
8. Measure affected queries and Livewire payloads.
9. Run targeted tests, architecture/localization, Pint, Larastan, and the
   package browser matrix.
10. Update canonical docs, evidence overlay, generated requirements, component
    inventory, UI migration matrix, implementation plan, and changelog.
11. Run the full serial release gate required by the blast radius.
12. Review the exact staged diff, commit one coherent package, and push main.

Stop the package rather than widening it when a missing prerequisite would
force duplicated authority, fake external integration, privacy leakage, or an
unbounded migration.

## Dependency Graph

```text
P00 -> P01
P01 -> P02 -> P05 -> P09 -> P11
P01 -> P03 -> P04
P05 -> P06 -> P07
P05 -> P08 -> P11
P02 + P03 -> P12 -> P13 -> P14
P12 + P14 -> P15 -> P16 -> P17 -> P18
P16 + P18 -> P19
P06 + P16 -> P20
P18 + P19 -> P21
P03 + P15 + P16 -> P22
P15 + P16 -> P23
P02 + P03 + P16 -> P24 + P25 + P27 + P28
P16 + P21 + P22 -> P26
P20 + P21 + P22 -> P29
P04 + P07 + P08 + P13..P29 -> P30
P05..P30 -> P31 -> P32 -> P33 -> P34 -> P35
```

## Stream A: Evidence And Portal Authority

### P00 - Freeze The Factual Inventory

Scope:

- Recompute route, page, model, migration, policy, component, factory, seeder,
  translation, and test inventories from the live checkout.
- Generate separate Point 12 and Point 13 status summaries by domain.
- Record every compatibility route, route collision, orphan view, and static
  prototype source.
- Correct the stale tracks/sessions statement in `docs/events/index.md` and
  the overclaimed type metadata in `docs/events/event-types.md`.

Acceptance:

- Every first-party route has a stable page ID, module, purpose, audience,
  authentication, authorization, active context, layout, navigation parent,
  breadcrumb, states, SEO/privacy class, query budget, tests, and status.
- No existing implementation is rebuilt until its evidence is classified.
- Generated requirement source checks remain deterministic.

Stop condition: any route cannot be classified without exposing an unresolved
authorization or ownership defect.

### P01 - Reconcile Existing Point 12 Evidence

Scope:

- Map the existing shell, navigation, authenticated boundary, directories,
  page headers, settings, notifications, messages, discovery, and module
  workflows to exact `portal.*` IDs.
- Add focused tests where behavior exists but is not independently proved.
- Promote only exact IDs supported by current code and fresh checks.

Acceptance:

- Point 12 no longer reports a misleading all-zero verified total.
- Every promoted atom references implementation, test, command, and result.
- Missing behavior remains planned with a named work package.

Stop condition: evidence relies only on a document, route name, enum, or
component existence.

### P02 - Canonical Organization Tenant

Progress: the organization authority foundation is implemented and verified in
`docs/plans/portal-organization-authority-foundation-work-package.md`. Active
organization selection remains P05; persisted places/organization locations
remain P03; downstream payment, marketplace, shelter, and competition Actions
must consume the role and restriction contract when those domains are added.

Scope:

- Introduce or adapt Organization, membership, role, invitation, location,
  verification, suspension, audit, and context records.
- Separate organization owner, administrator, finance, safety, event,
  marketplace, shelter, and read-only permissions.
- Add tenant-scoped Policies, active membership middleware/helpers, factories,
  seed identities, and an organization switcher contract.
- Backfill organization-like first-party data only from authoritative existing
  relationships; keep unknown ownership null and reviewable.

Acceptance:

- Wrong-organization and former-member access fails at query and Policy
  boundaries.
- Suspension can independently restrict publishing, payments, participant
  data, check-in, and invitations.
- Historical records retain attribution after membership removal.
- Organization-only events can use a real tenant authority.

Stop condition: ownership would need to be inferred from display text, email
domain, event creator, or marketplace activity.

### P03 - Canonical Place, Location, And Venue Authority

Scope:

- Audit static place presentation, group locations, booking locations, search
  sectors, device coordinates, shelter/foster locations, and event fields.
- Create one reusable persisted Place/Location boundary only where no current
  aggregate provides it.
- Add address, coordinates, region, verification source, visibility,
  accessibility facts, transport, parking, pet rules, and expiry metadata.
- Add event Venue, VenueArea, and room linkage without duplicating generic
  place identity.
- Add private-location grants, audited reads, expiry, revocation, and public
  approximate projections.

Acceptance:

- Public cards, metadata, maps, feeds, notifications, and exports cannot
  serialize exact private locations.
- Authorized attendees receive exact access only within configured windows.
- Venue facts distinguish verified, supplied, expired, and not assessed.
- Location changes revoke old grants and create material-change evidence.

Stop condition: a private home or foster address would become a searchable
place record.

### P04 - Public Website And Safe Public Projections

Scope:

- Define the explicit guest allowlist for public profile, directory, event,
  place, organization, result, and archive projections.
- Keep internal workspaces, participant lists, tickets, exact locations,
  messages, incidents, and eligibility private.
- Add public API Resources/presenters, canonical URLs, localized metadata,
  structured data, robots/sitemap policy, rate limits, and cache scopes.
- Resolve compatibility URLs with permanent or temporary redirects only when
  route evidence proves equivalence.

Acceptance:

- Guest responses are minimal and query-scoped before model resolution.
- Private/unlisted/organization/group/invitation objects do not leak through
  counts, suggestions, social images, sitemaps, or error differences.
- Authenticated deep links preserve intended destination after login.

Stop condition: public access depends on hiding controls instead of a separate
authorized projection.

### P05 - Canonical Context And Navigation

Scope:

- Finish route classification and page identity migration.
- Add canonical breadcrumbs, selected-pet context, organization context,
  contextual action slots, unread counters, and mobile navigation behavior.
- Persist context explicitly per user without granting access from stale IDs.
- Reauthorize context on every request and Livewire hydration.

Acceptance:

- Desktop and mobile navigation derive from one registry.
- Pet-required actions fail safely when no authorized pet is selected.
- Organization-required actions fail safely for wrong, removed, or suspended
  membership.
- Focus, expanded state, current page, long labels, and forced colors pass.

Stop condition: context switching changes authorization rather than only
selecting among already authorized resources.

### P06 - Notification Centre And Deep-Link Registry

Scope:

- Normalize notification categories, actor/subject metadata, read state,
  aggregate unread count, preferences, delivery channels, deduplication, and
  deep-link resolution.
- Reuse `ForumNotification` where compatible; migrate consumers instead of
  creating a second notification system.
- Add privacy-safe previews and destination reauthorization.

Acceptance:

- Unread counts remain consistent across header, centre, messages, and event
  workspace.
- Revoked/private destinations do not leak through previews or stale links.
- Category and quiet-hour preferences work, with audited emergency bypass.
- Notification storms are bounded and idempotent.

Stop condition: a notification requires embedding a ticket secret, exact
location, health status, minor data, or private phone number.

### P07 - Aggregated Calendar And External Feed

Scope:

- Define a provider contract for care tasks, medical appointments,
  vaccinations, bookings, event occurrences/sessions, volunteer shifts, and
  future organization schedules.
- Build one normalized read model or projection with source-owned mutations.
- Add locale/timezone views, filters, status changes, private-location rules,
  and revocable tokenized ICS feeds.

Acceptance:

- Calendar items deep-link to their source module and cannot mutate copied
  data.
- Cancellation, postponement, moved venue, selected session, and timezone
  updates appear consistently.
- External feeds contain minimized data and revoke atomically.
- Range queries and payloads have measured budgets.

Stop condition: calendar aggregation requires copying medical records,
eligibility details, or private event access into a shared public table.

### P08 - Global Search, Discovery, And Saved Searches

Scope:

- Create a typed search-provider registry for users, pets, organizations,
  groups, content, events, places, experts, marketplace, adoption, and
  lost-and-found where each provider owns visibility and projection.
- Add search scopes, result DTOs, filters, pagination, saved searches,
  recommendation explanations, fair exposure, sponsorship labels, and cache
  invalidation.
- Reuse current directory queries and discovery presentation where valid.

Acceptance:

- Block, privacy, tenant, pet-manager, private event, and exact-location rules
  are applied before counts and ranking.
- Entity search and command search remain semantically separate.
- Recommendations explain why they appear and sponsorship is explicit.
- Query plans and result payloads stay bounded under seeded volume.

Stop condition: a provider returns raw models, post-filters unauthorized rows,
or shares cached private results.

### P09 - Role Dashboards And Workspaces

Scope:

- Define a widget registry with stable keys, authorization, data owner,
  urgency level, query/payload budget, refresh behavior, and destination.
- Add owner, household, professional, organization, shelter, volunteer,
  moderator, and administrator dashboard compositions.
- Persist ordering/visibility preferences without allowing hidden urgent
  obligations to disappear.

Acceptance:

- Urgent welfare, medication, lost-pet, incident, payment, and moderation
  items outrank engagement metrics.
- Widgets query only their authorized tenant/context and avoid N+1.
- Empty, loading, stale, error, offline, and unavailable states are canonical.
- Mobile and keyboard workflows remain complete.

Stop condition: dashboard personalization can suppress mandatory safety or
legal work without a visible alternate location.

### P10 - Complete Settings Architecture

Scope:

- Consolidate account, profile, locale/timezone, accessibility, privacy,
  notifications, messaging, blocked users, selected contexts, pets,
  organizations, sessions/devices, integrations, exports, retention, and
  deletion into one settings information architecture.
- Reuse existing profile settings and domain-specific Policies/Actions.

Acceptance:

- Every setting names its scope and effect before mutation.
- High-risk changes require password confirmation or equivalent step-up.
- Export/deletion requests preserve legal, payment, incident, and audit
  retention requirements.
- Settings are translated, keyboard accessible, and mobile complete.

Stop condition: one setting silently overrides a stricter module policy.

### P11 - Command Palette, Quick Actions, And Feed Integration

Scope:

- Add a server-owned command registry for navigation, search, pet switch,
  organization switch, and authorized non-destructive shortcuts.
- Add a class-based Livewire command palette with bounded debounced search,
  keyboard focus management, mobile access, stale-command prevention, and
  confirmation for destructive actions.
- Add contextual quick actions and reuse canonical feed item/event card
  projections.

Acceptance:

- Commands never execute solely from a text match.
- Every action reauthorizes current pet, organization, and resource.
- Palette snapshots remain small and expose no secrets.
- Repeated navigation does not duplicate listeners or lose focus.

Stop condition: a command bypasses the underlying route, Policy, Action, or
confirmation requirement.

## Stream B: Complete Event Core

### P12 - Complete Event Type Registry

Scope:

- Replace capability fragments with one typed metadata definition per
  canonical type: translated description, category, organizer types,
  participant model, pet model, fields, requirement/safety templates,
  scheduling, ticket/session/competition/online/recurrence/directory support,
  icon, status defaults, factory state, seed scenario, and tests.
- Preserve legacy values through explicit compatibility mappings.

Acceptance:

- Builder, validation, directory, factories, and tests consume the same
  registry.
- Custom types use a controlled base template.
- High-risk types require verified organizer and safety review.
- Harmful, exploitative, live-animal sale, betting, and gambling definitions
  cannot be configured.

Stop condition: type behavior is duplicated in Livewire, Blade, factory, and
Action conditionals.

### P13 - Event Builder, Drafts, Templates, Versions, And Approval

Scope:

- Create a canonical class-based Livewire wizard and form object covering all
  type-driven sections.
- Add idempotent draft save/resume, missing-section state, preview modes,
  organization templates, material versions, publication readiness, approval
  decisions, and publication Action.
- Sanitize structured rich content and secure event media.

Acceptance:

- Draft, autosave, repeated submission, locale/context preservation, preview,
  duplication, template versioning, and approval tests pass.
- Publication fails closed on missing organizer, location/format, capacity,
  pet, safety, privacy, accessibility, media, ticket, and contact data.
- Registration retains the exact accepted event/rule/consent versions.

Stop condition: a type-specific section stores arbitrary organizer code,
validation expressions, unsafe HTML, or unreviewed external embeds.

### P14 - Recurrence And Occurrence Truth

Scope:

- Add validated recurrence rules, occurrence generation, additional/skipped
  instances, one-instance and future-instance edits, DST handling, and
  material-change propagation.
- Keep series defaults and instance overrides independently versioned.

Acceptance:

- Daily, weekly, selected weekday, monthly, custom interval, fixed list,
  all-day, overnight, multi-day, DST, skipped, moved, and cancelled tests pass.
- Past occurrences never change when future defaults change.
- Registration and capacity scopes are explicit per occurrence or series.

Stop condition: recurrence is represented only by an unvalidated free-form
string or silently rewrites historical occurrences.

### P15 - Venue Areas, Routes, Group Walks, And Weather

Scope:

- Connect events to canonical venues and areas.
- Add route geometry, public approximation, private operational route,
  distance, elevation, terrain, crossings, rest/water/exit points, emergency
  access, route groups, leaders, equipment, and accessibility facts.
- Add normalized weather plan, source observations, thresholds, decision
  authority, and continue/shorten/reroute/move/postpone/cancel actions.

Acceptance:

- Public route and private operational details never cross privacy scopes.
- Route groups enforce separate participant/pet capacity and leaders.
- Weather changes update event state, communications, calendar, tickets, and
  private grants transactionally where required.
- Group-walk mobile and text-only route interfaces pass browser checks.

Stop condition: safety depends on a map marker without accessible directions
or on an external forecast treated as guaranteed truth.

### P16 - Requirement Registry, Eligibility, Evidence, And Exceptions

Scope:

- Add typed event requirements with hard/recommendation semantics, source,
  applicability, evidence type, validity, privacy, retention, reviewer, and
  version.
- Add participant/pet eligibility decisions, minimal verified statuses,
  document links, exception requests/conditions/expiry, and revalidation.
- Reuse pet authority, credentials, vaccination, medical document, and secure
  document boundaries without copying complete records.

Acceptance:

- Missing, expired, disputed, unknown, not assessed, and manual-review states
  never pass as eligible.
- Organizers see only status and necessary evidence, not unrelated medical
  history.
- Exceptions are requirement-specific, audited, expiring, and independently
  authorized.
- Changed requirements identify and notify affected registrations.

Stop condition: a generic bypass permission or automated risk score can
approve/reject an animal or participant.

### P17 - Complete Registration Model

Scope:

- Extend registration to individuals, households, several pets, guest pets,
  teams, organization delegations, volunteers, exhibitors, vendors, speakers,
  trainers, judges, observers, online attendees, and waiting-list requests.
- Add registration participants, responsible adult/guardian, structured
  question schemas, answers, documents, selected sessions, role, transfer, and
  complete snapshots.

Acceptance:

- Duplicate scope is database constrained by occurrence, role, participant,
  pet/team, and ticket as applicable.
- Guest pets remain event-limited and do not become public profiles.
- Minor identity, recording consent, check-in/release responsibility, and
  attendee-list privacy are explicit.
- Editing, withdrawal, rejection, conditional approval, transfer, and renewed
  acceptance preserve history.

Stop condition: organizer-defined questions lack purpose, type, validation,
privacy, retention, and translations.

### P18 - Capacity Pools, Reservations, And Waitlists

Scope:

- Add typed capacity pools for humans, animals, species, tickets, sessions,
  categories, teams, vendors, volunteers, parking, accessibility, rooms, and
  route groups.
- Add atomic holds, expiry, release, fair waitlist policy, offer windows,
  preserved pricing, revalidation, and capacity change decisions.

Acceptance:

- Concurrency tests prove one final place cannot be double allocated.
- Checkout holds expire and release idempotently.
- Waitlist ordering semantics are visible and never presented as a guarantee.
- Capacity reduction follows a documented policy and preserves accessibility
  allocations, refunds, notification, and audit.

Stop condition: capacity is enforced only by preflight counts or a client-side
timer.

### P19 - Tickets, Payments, Refunds, Receipts, And Donations

Scope:

- Build provider-independent TicketType, TicketReservation, Ticket,
  EventPayment, EventRefund, receipt/invoice, fee, discount, donation, payout,
  and webhook idempotency models.
- Reuse the canonical payment boundary if one is completed elsewhere; do not
  infer provider success from browser return data.
- Separate eligibility approval, capacity hold, payment, ticket issuance, and
  check-in state.

Acceptance:

- Free and paid ticket flows share capacity and confirmation truth.
- Price summary shows base, tax, platform/organizer/ticket fees, options,
  donation, discount, and total before authorization.
- Amount, currency, provider signature, event ID, and idempotency are checked
  server-side.
- Full/partial/failed/reversed refunds and material-change consequences pass.
- Donation is optional, separate, identifies beneficiary, and never affects
  eligibility, judging, or adoption.

External gate: paid checkout cannot be enabled until a provider, merchant
region, credentials, webhook contract, tax responsibility, payout policy, and
refund liability are selected and documented. The internal ledger and free
ticket path may be completed before that decision.

### P20 - Announcements And Event Conversations

Scope:

- Add targeted, versioned announcements with occurrence, audience, priority,
  locale, channels, delivery, deduplication, and acknowledgement.
- Reuse canonical messaging for participant questions, registration support,
  volunteers, staff, sessions, officials, and emergencies.
- Add privacy-safe preview generation and separate marketing consent.

Acceptance:

- Audience counts and deliveries match confirmed, waitlisted, checked-in,
  format, ticket, session, route, role, vendor, volunteer, speaker, judge, and
  staff scopes.
- Material changes cannot be communicated only through a social comment.
- Emergency bypass requires role, reason, action, audience, and audit.
- Participants never receive a complete contact list by default.

Stop condition: event registration is treated as permanent marketing consent.

### P21 - Tickets, QR, Check-In, Offline Sync, And Attendance

Scope:

- Add signed/tokenized event-scoped QR credentials, revocation, re-entry,
  manual search, participant/pet identity checks, check-out, no-show, and
  attendance corrections.
- Add minimized encrypted offline packages, operator/device sessions,
  expiration, idempotent synchronization, conflict resolution, and local data
  deletion protocol.

Acceptance:

- QR contains no raw personal data or reusable ticket secret.
- Duplicate scans show the previous attendance record and cannot create a
  second active check-in.
- Screenshot transfer fails where identity is required.
- Offline UI never claims server completion before synchronization.
- Corrections preserve original status, operator, reason, time, and effects.

Stop condition: offline data contains full medical records, emergency contacts,
or unrestricted participant exports.

### P22 - Safety Plans, Incidents, Medical And Lost-Pet Escalation

Scope:

- Add normalized safety plan, risk assessment, reviewer decision, expiry,
  weather linkage, and scoped stop/suspend Actions.
- Add factual incident, source statement, participants, animals, witnesses,
  media, immediate action, status, reviewer, follow-up, and escalation links.
- Integrate urgent cases with medical response, lost-and-found/search case,
  route/last-location, moderation, check-in reconciliation, and emergency
  communication.

Acceptance:

- Initial records separate observation, participant statement, organizer
  statement, professional assessment, and unknown.
- No event workflow diagnoses an animal or assigns final fault automatically.
- Authorized staff can stop a session, category, pet participation, booth, or
  event without deleting data.
- Welfare-related early departure is neutral in attendance analytics.
- Removed participants lose access without public shaming.

Stop condition: an AI output, payment, popularity, or organizer ownership can
make a final safety, medical, eligibility, or fault decision.

## Stream C: Specialized Event Domains

### P23 - Training Sessions And Workshops

Scope:

- Add training class, method disclosure, level, prerequisite, lesson plan,
  equipment requirements/prohibitions, controlled interaction, adaptations,
  welfare stop criteria, completion notes, resources, and next-level
  eligibility.
- Reuse professional profile and credential verification.

Acceptance:

- Harmful methods/equipment fail publication and safety review.
- Private adaptations do not expose medical/behavior details to participants.
- Attendance alone cannot produce a professional credential.
- Trainer, handler, safety lead, or welfare officer can stop participation.

### P24 - Exhibitions, Shelter Events, And Adoption Integration

Scope:

- Add exhibitor applications, display plans, areas, environmental limits,
  rest/water/noise/temperature controls, public interaction rules, and welfare
  removal.
- Connect adoptable animals to adoption profiles, applications, meetings, and
  controlled handover while preserving foster privacy.

Acceptance:

- Exhibitor, vendor, sponsor, speaker, and competition roles remain separate.
- Adoptable animals cannot enter marketplace purchase, bidding, or impulse
  handover flows.
- Welfare staff can remove one animal from display without cancelling the
  whole event.

### P25 - Conferences, Speakers, Materials, And Online Access

Scope:

- Add speaker invitations, profile disclosure, tracks/sessions, materials,
  scoped online grants, moderator tools, questions, chat policy, recording,
  captions/transcripts, sponsor disclosure, and verified accreditation claims.

Acceptance:

- Session links are participant-scoped, expiring, revocable, and absent from
  public source.
- Recording scope, retention, participant media, consent, captions, and access
  are explicit before registration.
- Sponsored content is labeled and cannot appear as independent professional
  advice.

### P26 - Competitions, Judging, Scoring, Results, Appeals

Scope:

- Add competition, categories/divisions, entries, judge assignments,
  conflicts, scaled criteria, independent scores, corrections,
  disqualification, welfare withdrawal, deterministic finalization, result
  versions, appeals, prizes, certificates, and anti-cheating review signals.

Acceptance:

- One judge cannot overwrite another; uniqueness is database enforced.
- Finalization locks rows, verifies required scores, handles ties
  deterministically, versions results, and blocks concurrent finalization.
- Conflicted judges cannot score connected entries.
- Welfare withdrawal has a neutral public status.
- No wager, stake, betting payout, or gambling feature exists.
- Anti-cheating signals require human review and do not create invisible risk
  scores or automatic accusations.

### P27 - Vendors And Sponsors

Scope:

- Add seller-backed vendor applications, category/document/insurance review,
  booths, setup/teardown, equipment/electricity, safety inspection, fees,
  deposits, sponsor contracts, placements, contribution, prize, and disclosure.
- Reuse marketplace verification and prohibited-product rules.

Acceptance:

- Event participation cannot weaken seller verification or product policy.
- Booth fee, ticket, deposit, product sale, donation, and sponsorship remain
  separate ledger purposes.
- Sponsors cannot access attendee data or influence safety, adoption, or
  judging without explicit lawful role and disclosure.

### P28 - Volunteers And Staff Shifts

Scope:

- Add volunteer roles, qualification requirements, shifts, capacity,
  supervisor, breaks, instructions, assignments, waitlist, transfer,
  cancellation, check-in/out, no-show, completion, and incident scope.
- Reuse organization membership and professional credentials where required.

Acceptance:

- Ordinary participants cannot see volunteer contact, address, private
  schedule, safety notes, or documents.
- Removed/cancelled volunteers lose current operational access while history
  remains attributable.
- Briefing, emergency contacts, partner requirements, rest, and safe refusal
  are represented.

### P29 - Media, Privacy, Feedback, Reports, Certificates, And Archive

Scope:

- Add versioned media policy/consent, guardian consent, restricted-use and
  staff indicators, moderated event gallery, alt text, removal/report flow,
  attendee exports, explicit retention, and former-staff revocation.
- Add typed private/public feedback, verified-attendance review markers,
  organizer final report, resources, lost property, result/certificate
  delivery, and archive rules.

Acceptance:

- Invitation/organization-only media cannot become public through a generic
  gallery action.
- Minor identity and media remain private without explicit lawful consent.
- Exports are purpose-bound, minimized, audited, expiring, and exclude health
  and emergency detail by default.
- Certificates are versioned, result-linked, localized, securely downloadable,
  and correctable/revocable through audit.
- Archived pages cannot accept registration.

## Stream D: Integration, Interface, Evidence, And Release

### P30 - Complete Event Portal Integration

Scope:

- Add event directory provider to global search/discovery, fair exposure,
  explanations, sponsorship labels, saved events/searches, feed cards, public
  metadata, notifications, calendar items, and related-event projections.
- Add care preparation/follow-up task suggestions, document/media links,
  adoption meetings, marketplace vendors, specialist roles, organization
  workspaces, and lost-pet/medical escalation deep links.

Acceptance:

- Current event status is identical across cards, detail, calendar,
  notifications, tickets, search, metadata, and feeds.
- Private/unlisted events do not leak through counts, facets, maps, organizer
  profiles, sitemaps, or recommendation explanations.
- Source modules remain authoritative for care, medical, adoption, payments,
  messages, files, media, and moderation.

### P31 - Repository-Wide UI Migration

Scope:

- Audit every event, meetup, calendar, appointment, booking, ticket, payment,
  volunteer, competition, check-in, organization schedule, and notification
  page against the UI migration matrix.
- Preserve/improve canonical components for page headers, status, cards,
  wizard, fields, validation summary, responsive table/list, schedule, ticket,
  check-in, results, incidents, cancellation, states, dialogs, drawers, and
  mobile action bars.
- Remove genuine duplicates and obsolete CSS/JavaScript only after every
  consumer migrates.

Acceptance:

- No duplicate canonical event card, ticket, schedule, or check-in component.
- No nested cards, horizontal overflow, dynamic Tailwind classes, unlabelled
  icons, color-only state, focus loss, or viewport-breaking dialogs.
- Long EN/LT/RU strings, 200% text, reduced motion, forced colors, desktop,
  tablet, and mobile pass.

### P32 - Complete Factories And Demo World

Scope:

- Add a valid factory and meaningful states/helpers for every affected model.
- Build the required 60 event scenarios using stable existing users, pets,
  organizations, shelters, experts, sellers, places, payments, messages,
  notifications, documents, media, lost-pet cases, and moderation records.
- Keep fixed seeders idempotent and demo identities environment-gated.

Acceptance:

- Fresh migration plus seed and repeated seed pass without count drift.
- Production execution creates no demo identities or scenarios.
- Relationship helpers create only explicit, bounded graphs.
- High-volume registration/check-in/scoring fixtures are opt-in and
  deterministic.

### P33 - Complete Automated, Browser, Security, And Performance Proof

Scope:

- Implement all Point 12 and Point 13 route, lifecycle, creation, recurrence,
  venue, eligibility, registration, concurrency, ticket/payment, schedule,
  specialized-domain, communication, check-in, cancellation, incident,
  privacy, localization, accessibility, architecture, browser, and performance
  tests.
- Add query and Livewire payload budgets for every critical page.

Acceptance:

- Every protected route and direct mutation has positive and negative actors,
  wrong tenant, removed role, suspension, block, stale ID, and replay coverage.
- Concurrency tests cover final capacity, waitlist offer, checkout, transfer,
  check-in, session reservation, scoring/finalization, cancellation, and
  refund initiation.
- Browser flows cover discovery through archive at desktop/mobile with focus
  restoration, repeated `wire:navigate`, no duplicate listeners, no overflow,
  and no console errors.
- Public/private cache invalidation and no-N+1 checks pass under seeded volume.

### P34 - Documentation And Atomic Evidence Closure

Scope:

- Synchronize every event and portal canonical document, global architecture,
  domain/data/security/authorization/frontend/testing/seeding/performance docs,
  route/page/workflow registries, component inventory, UI migration matrix,
  implementation plan, compliance matrix, changelog, deployment, and rollback.
- Add exact evidence overlay entries per completed requirement manifest and
  regenerate the 38,377-record catalogue deterministically.
- Produce the complete Point 12 and Point 13 final report with factual counts.

Acceptance:

- No stale implementation paths, counts, status claims, placeholders, or TODO
  language remains.
- Every `portal.*` and `event.*` atom has a factual final status and evidence
  appropriate to that status.
- Immutable source checksum and generated files pass their checks.

Stop condition: documentation claims a provider, aggregate, browser flow,
locale, RTL mode, or test result that was not observed.

### P35 - Final Release, Rollout, And Recovery

Scope:

- Run Composer validation/audit, dependency compatibility, syntax, Pint,
  Larastan, full serial Pest, architecture/localization, complete migration
  lifecycle, fresh migration/seed/repeat seed, NPM audit, production build,
  config/event/route/view cache, browser matrix, performance budgets, secret
  scan, and final diff review.
- Rehearse expand/contract deployment, queues/scheduler, provider webhooks,
  cache invalidation, private-data rollback, event suspension, payment/refund
  recovery, offline check-in reconciliation, and result correction.

Acceptance:

- Every required gate has an exact command, total, result, and retained
  artifact where appropriate.
- No critical console, accessibility, security, query, payload, migration,
  seeding, or data-retention defect remains.
- The staged diff is attributable, passes `git diff --cached --check`, commits
  cleanly on main, and pushes fast-forward to `origin/main`.

Stop condition: any quality gate fails, an external integration is unverified,
or rollback/recovery cannot preserve payments, registrations, attendance,
results, incidents, consent, and audit history.

## Per-Package Required Test Actors

Use the relevant subset in every package and the complete set before release:

- guest, active verified user, inactive user, unverified user;
- registered participant, unregistered user, former participant;
- event creator, owner, primary organizer, co-organizer, schedule manager,
  registration manager, ticket manager, payment reviewer, check-in operator,
  safety lead, welfare officer, trainer, speaker, judge, scorekeeper, vendor,
  volunteer, auditor, removed staff, former organizer;
- organization administrator, correct member, wrong organization, suspended
  organizer;
- blocked participant and linked-account adversary;
- moderator with active assignment, moderator without a case, administrator.

## Required Final Measurements

Record, compare, and enforce budgets for:

- public event directory and detail;
- authenticated directory/workspace;
- builder autosave/publication preview;
- registration and checkout;
- ticket and refund pages;
- agenda, track, room, and selected-session views;
- organizer dashboard and registration queue;
- high-volume manual/QR/offline check-in;
- volunteer/vendor/speaker workspaces;
- competition scoring and public results;
- global search, command palette, notifications, dashboard, and calendar;
- Livewire snapshot sizes, pagination, cache hit/miss, and invalidation.

Budgets are established from measured fixtures before a feature is promoted;
the plan does not invent thresholds without data.

## Definition Of Complete

The project is complete for Point 12 and Point 13 when:

1. All 3,449 `portal.*` and 4,968 `event.*` atomic requirements have factual
   final statuses with evidence.
2. Every implemented package passes its exact functional, policy, validation,
   localization, accessibility, concurrency, browser, and performance gates.
3. Every genuine external blocker names the provider/legal/runtime dependency
   and does not masquerade as completed functionality.
4. No parallel portal or event authority, duplicate component system,
   disconnected prototype, placeholder workflow, fake payment, fake ticket,
   fake score, or unfinished migration remains.
5. Documentation, generated matrices, seed world, production build, complete
   tests, deployment, recovery, commit, and push all match the live code.
