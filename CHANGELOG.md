# Changelog

## Unreleased - 2026-08-01

### Security

- Converted PawCircle into a closed authenticated portal: anonymous product
  pages, mutations, token shares, Livewire uploads/previews, and product media
  now fail before route-model binding. Active verified accounts retain normal
  policy-scoped access; inactive and unverified sessions fail closed.
- Disabled direct local storage serving and public storage-link generation;
  forum topic, lost/found, sighting, and marketplace media now stream through
  an authenticated, MIME-bounded, canonically contained route with traversal
  and symlink-escape regressions.
- Centralized private file responses behind canonical owning-directory checks
  that reject traversal segments, foreign disks, cross-domain stored paths,
  missing files, and symbolic-link escapes before streaming or audit changes.

### Interface System

- Extended the dedicated `/neighbors` EN/LT/RU contract from 71 to 160 leaves
  for the complete Ari neighbor profile; extracted its presentation from the
  broad preview service into a zero-query presenter, moved the walk action out
  of Blade, and added canonical Lucide section, pet-metadata, routine, and
  community icons with responsive browser coverage.
- Added a dedicated 42-leaf EN/LT/RU contract for `/share/{target}`; stable
  target and channel codes now drive localized page, delivery, recipient,
  detail, privacy, subject, and message copy, while every share action retains
  the canonical Lucide icon system and a 44-pixel interaction target.
- Extended the dedicated `/messages` EN/LT/RU contract from 329 to 364 leaves
  for the complete call stage and responsive details return path; call state
  now persists stable type/status/quality codes instead of translated text,
  a class-based Blade component prepares all control/icon maps, and the real
  details route exposes its context panel below 1200 pixels instead of hiding
  the requested content.
- Extended the dedicated `/messages` EN/LT/RU system contract from 132 to 329
  leaves for all eight conversation metadata sets, the complete right context
  rail, and every mutation/error feedback family; replaced locale-dependent
  notification headlines with stable localized levels, added a canonical
  sender-identity icon, and raised every context search, disclosure, safety,
  poll, task, and shared-content target to at least 44 pixels.
- Extended the dedicated `/messages` EN/LT/RU system contract from 61 to 132
  leaves for the composer, attachment tools, message types, reactions, audio
  controls, per-message actions, and sent/read/delivered states; kept eight
  attachment and twenty message-type codes stable while assigning canonical
  Lucide icons; and made the browser matrix open the real conversation view
  below 832 pixels so mobile composer targets are measured while visible.
- Extended the dedicated `/messages` EN/LT/RU system contract from 32 to 61
  leaves for the page shell, thread header, request gate, professional state,
  channels, message log, and empty states; added the canonical channel fallback
  icon; and raised every audited thread menu, audio, attachment, quiet-send,
  search-clear, channel, and scheduling target to a 44-pixel hit area.
- Localized the complete `/places` directory system surface through a dedicated
  188-leaf EN/LT/RU contract; kept filter, mode, view, and layer codes stable;
  resolved generalized-location labels in the active locale instead of
  persisting translated copy; and unified category, mode, and map-layer
  controls on canonical Lucide icons with 44-pixel-safe comparison links and
  diacritic-safe map headings.
- Localized the `/messages` system directory surface through a dedicated
  32-leaf EN/LT/RU contract for folders, search, empty states, conversation
  types, and relative dates; preserved member-authored names and message bodies
  verbatim; and replaced clipped mobile folder labels with canonical Lucide
  icons above wrapping, 44-pixel-safe labels.
- Localized the complete `/neighbors` directory and four first-party fixtures
  through a dedicated EN/LT/RU contract; made category filters and numeric
  distance sorting independent of translated display text; added canonical
  Lucide category badges; and extended the responsive browser localization
  ratchet across all priority viewports and accessibility modes.
- Added typed, visibility-aware pet alternative names and automatic previous-
  name history; the Basics workspace now manages aliases, the public profile
  exposes only public alternatives, and manager search resolves an accessible
  old name without changing the pet's stable identity or adjacent links.
- Hardened shared card fallbacks for unsupported heading levels, spacing, and
  media ratios; localized the group fallback action; exercised group cards with
  long copy across EN/LT/RU and six viewports; and reused the typography leaves
  in discovery and expert cards without forcing them into the directory shell.
- Rebuilt the `/groups` compatibility cards on shared contained media, opaque
  body, semantic heading/description, statistics, and bottom-footer primitives;
  migrated the direct pet, neighbour, and meetup card family and added
  six-width browser geometry regressions so future spacing and separation
  changes have one maintenance boundary.
- Added one canonical Lucide icon primitive with bounded sizes, consistent
  stroke/fill and ARIA behavior; migrated all direct/dynamic consumers and
  legacy selectors; added icons to primary navigation and unambiguous actions;
  and introduced a zero-debt downward-only audit.

### Events

- Added the canonical organization tenant authority with current memberships,
  signed account-bound invitations, independent event, finance, safety,
  marketplace, shelter, and audit roles, eight operational restrictions,
  immutable attribution, localized Livewire workspaces, guarded demo data, and
  organization-only event creation, visibility, registration,
  participant-data, invitation, and check-in boundaries.
- Kept organization invitation tokens out of public Livewire state and
  minimized member email and internal restriction projections by policy.

### Places

- Replaced the twelve-fixture action target allow-list with bounded canonical
  identifier validation and policy-scoped Eloquent resolution. Added shared,
  actor-attributed place questions and one manager-authorized official answer
  with database idempotency, cross-account presentation, EN/LT/RU feedback,
  factories, and regression coverage.
- Replaced the static-only place identity boundary with policy-scoped Eloquent
  places, canonical venues, reversible indexed schema, idempotent demo seeding,
  and dynamic detail URLs for newly submitted places.
- Preserved the full server-rendered search, map alternative, emergency clinic
  mode, EN/LT/RU presentation, and encrypted account-level place state while
  making new submissions immediately searchable to their authorized author.
- Added end-to-end action coverage for saves, follows, visits, expiring private
  check-ins, corrections with evidence, temporary warnings, reviews, and
  questions; removed visible prototype wording from operational place tabs.
- Added encrypted exact-location grants and reveal audits, event/venue linkage,
  confirmed-registration access checks, responsive card-height regression
  gates, and a corrected park-specific Vingis cover image.

### Pet Profiles

- Added structured identifying marks with ten controlled feature types,
  encrypted per-row descriptions, reversible retirement, and explicit public
  or private-verification visibility. Public profiles load only active public
  rows; friend, clinic, and active-search audiences remain unavailable until
  their authoritative access boundaries are implemented.
- Added a species-neutral structured appearance catalogue with primary and
  additional colors, spots/stripes/gradient patterns, and optional feather,
  scale, and seasonal clarification; public profiles now render a localized
  visible description while private identifying marks remain manager-only.
- Added normalized breed-origin profiles with one, mixed, possible-multiple,
  no-breed, and unknown states; up to four entries now retain independent
  confidence, provenance, and optional mixed percentages while legacy breed
  strings remain compatible and photographs cannot upgrade trust.
- Added typed species confidence for confirmed, possible, and unidentified
  animal groups. Possible cat/dog selections remain normalized for discovery
  while every public and manager-facing projection labels the guess honestly.
- Added a bounded policy-visible duplicate review before pet creation, safe
  possible-match cards, encrypted typed access requests, manager evidence
  review, and invitation-based access activation. Generic review cannot
  approve an ownership transfer, and private profiles or facts never enter the
  candidate projection.

- Added idempotent change/blur autosave to the seven ordinary descriptive
  profile steps while retaining explicit submission for photos, managers,
  privacy, protected identifiers, and lifecycle transitions. Saving, unsaved,
  validation, offline, and retry states remain server-authoritative and
  accessible.
- Added revision-aware, page-memory reconnect recovery: a failed descriptive
  save remains pending, retries once when connectivity returns, and clears
  only after the server acknowledges the same edit revision, without copying
  private profile values into persistent browser storage.
- Replaced the `/pets` nearby-pet prototype with a policy-scoped personal
  workspace for owned and actively shared profiles, server-side search,
  filters, sorting and pagination, pending invitation review, protected media,
  real management/profile deep links, and EN/LT/RU responsive states.
- Pinned `/pets` to the explicit server pagination view so an earlier Livewire
  render cannot replace page links with client-only pagination controls.
- Replaced the legacy all-at-once management screen behind the canonical
  `/compose/pet` creation journey with twelve URL-addressable, independently
  saved profile steps in the main content column.
- Added a responsive central navigator, mutation-free skipping, honest
  saved/optional states without a disclosure score, step explanations, and a
  contained mobile scroll-snap row that keeps the active form nearby.
- Added allowlisted optimistic partial updates and one private encrypted,
  versioned microchip record; roles without the critical permission receive no
  value, completion signal, editable field, or mutation control.

### Discovery

- Replaced the hard-coded `/discover` demo, fictional query, local pulse,
  trending topics, and weekend promotion with bounded database-backed events,
  communities, places, specialists, public pets, members, and post recommendations.
- Added dynamic minimized member profiles with stable actor keys, public pet
  links, audience-scoped posts, independent block checks, and no private account fields.
- Added factual recommendation reasons, validated URL filters, canonical module
  deep links, user-owned hide/reset preferences, and account/actor block plus
  `is_recommendable` enforcement without exposing exact private locations.
- Standardized discovery on shared page, status, media, action, notice, and
  empty-state components; added EN/LT/RU responsive layouts and a dedicated
  desktop/mobile browser gate.
- Verified the release with a 2,657-test / 84,589-assertion serial suite, fresh
  and repeat seeding,
  bounded query measurement, static analysis, formatting, dependency audits,
  production build, and desktop/mobile/long-Lithuanian browser checks.

- Added occurrence-scoped event tracks, physical/online rooms, sessions, and
  public/private staff assignments with controlled status/type/reservation
  enums, encrypted private room and conflict data, independent capacities,
  and explicit model factories.
- Added one policy-authorized transaction for idempotent schedule creation and
  editing. It locks event resources, validates occurrence/timezone/capacity and
  event-team scope, rejects room/track/staff overlaps, and permits only an
  owner-level documented audit override.
- Added the canonical responsive event schedule and class-based Livewire
  manager inside the existing event workspace, with EN/LT/RU labels, hidden
  drafts/private staff for ordinary viewers, and an idempotent multi-track
  conference demo scenario.

### Forum And Animal Taxonomy

- Redesigned `/forum/ask` as one responsive authoring surface, moved the full
  publishing checklist above the form, removed the detached right sidebar, and
  grouped every preserved field into clear context, response, and optional
  media sections with reviewed EN/LT/RU guidance.
- Made `ForumTopicFactory` portable across fresh server installs by using the
  factory's injected Faker generator instead of the optional global `fake()`
  helper.
- Verified all 49 Phase 4 One-Health category atoms from immutable source,
  including the exact 42-child hierarchy, and added the missing localized
  physician, veterinarian, public-health, and emergency-service boundary. The
  versioned category-tree cache carries the notice with zero additional
  database queries, and desktop/mobile Chrome confirms the selected category.
- Moved the complete forum category navigator out of the narrow left sidebar
  and into the main discussion column. The selected root now presents its
  localized purpose and every direct subcategory in a responsive, keyboard-
  complete grid while preserving search, language, filter, sort, pagination,
  aliases, and stable category URLs. Source-derived subcategory labels receive
  sentence capitalization without rewriting the immutable taxonomy manifest.
- Verified all 59 Phase 4 wildlife/coexistence category atoms from immutable
  source through synchronized keys, slugs, positions, and reviewed EN/LT/RU
  root rows while retaining, but not promoting, the two Phase 7 reporting
  atoms. Production schema, routes, presentation, cache, and query count are
  unchanged.
- Verified the complete special-needs/accessibility category hierarchy and
  hardened category localization so only reviewed target/fallback values can
  replace immutable source text; seed and administrative invalidation retain
  locale-isolated zero-query warm caches.
- Hardened the source-derived forum category catalogue against checksum,
  version, count, format, hierarchy, and duplicate-key/slug corruption; the
  first exact Phase 4 category slice now proves the complete before-ownership
  hierarchy. Warm localized tree reads dropped from 2 database queries to 0.
- Added an isolated full migration lifecycle verifier that proves every one of
  the 118 migration files applies, rolls back to an empty ledger, reapplies,
  and accepts the complete seed twice. Added source guards for typed reversible
  methods and forbidden raw SQL, closing the final 13 Phase 3 requirements.
- Completed the executable topic-type schema boundary: one typed catalogue
  now owns versioned fields and capability metadata, a bounded shared cache
  invalidates on definition changes and synchronization, generic topic writes
  retain the resolved schema version, and location/species/media, rating,
  accepted-answer, and notification rules fail closed at server boundaries.
- Added database-enforced vote/reaction values, typed enum casts, and a
  policy-authorized versioned/idempotent moderation-case closure transaction;
  corrected competing single-answer acceptance to retain the current
  canonical answer.
- Reconciled the configurable forum topic-type foundation against the source:
  26 source-listed stable types have active versioned definitions and EN/LT/RU
  translation contracts; repeated synchronization preserves IDs, attached
  topics, structured JSON, and custom definitions. Added an executable schema
  contract and exact evidence for 28 Phase 3 persistence requirements without
  claiming the still-open runtime capability atoms.

- Preserved and atomized the complete Point 7 medical-record revision into
  3,867 requirements, linked medical records to canonical pet identity,
  enforced one linked record per pet, contracted stale-owner access after
  transfer, added explicit allergy/medication knowledge states, and retained
  compatible encrypted legacy records, grants, documents, and audit history.

- Established the canonical pet-profile foundation with permanent identity,
  taxonomy links, typed lifecycle, timed multi-manager roles and permissions,
  layered privacy, append-only actor history, versioned encrypted facts,
  idempotent legacy backfill, stable URLs/QR, and localized accessible
  class-based Livewire create/manage/public workflows.
- Replaced the legacy `/compose/pet` prototype with the canonical protected
  pet-creation flow, reduced the first save to four validated essentials, and
  added a responsive private-draft interface with direct Add pet links,
  EN/LT/RU guidance, accessible controls, and progressive completion notes.
- Added an optional processed primary photo to first pet-profile save, backed
  by canonical private media assets, a single-current placement constraint,
  `manage-media` authorization, idempotent replacement, 30-day logical removal
  and restoration, and a canonically contained policy-protected media route.
- Established the canonical social-relationship foundation with typed actor
  adapters, directed and symmetric edges, recipient-controlled consent,
  idempotent request lifecycle, profile-level safety controls, immutable real
  user audit, bounded backfill/search, and a localized accessible Livewire
  relationship center.

- Preserved the combined forum, taxonomy, pet, social, content, communication,
  community, and medical specification with a deterministic SHA-256 and
  generated 29,960 source-linked atomic requirements without renumbering the
  existing catalogue.
- Audited the preview feed and persistent photo/forum boundaries, then defined
  the phased canonical publication, audience, media, distribution,
  moderation, integration, accessibility, and resilient-delivery architecture
  for all 4,011 content requirements.
- Added a deterministic 44-root, 1,637-subcategory forum hierarchy with
  stable keys, translations, aliases, redirects, safe synchronization, topic
  backfill, and cache invalidation.
- Added versioned animal taxonomy sources, names, identifiers, changes,
  domestic classifications, breed registries, community groups, and a
  checksummed chunked import pipeline with analysis, resume, validation,
  activation, rollback, and conflict-safe history.
- Added scoped append-only reputation, audited trust and badges, qualified
  confirmations, accepted-answer history, unified reports, moderation cases,
  actions, appeals, recusal, and private evidence.
- Added the authorized moderation operations UI for triage, case assignment,
  audited actions, conflict recusal, and independent appeal review, including
  linked report state synchronization and mobile-safe tables.
- Added independent professional credential review with expiry, suspension,
  appeal, audit events, and authorized class-based Livewire administration.
- Added structured adoption/foster cases, encrypted idempotent applications,
  controlled review/adoption/follow-up/return/foster transitions,
  append-only history, taxonomy links, policy enforcement, localized Livewire
  UI, and an atomic bridge from marketplace reports to unified moderation.
- Connected adoption provider identity to independent purpose-compatible
  credential review with owner isolation, natural expiry, rejection,
  suspension, revocation, appeal propagation, private evidence boundaries,
  and an idempotent backfill path.
- Extended lost/found into structured owned-pet cases with global taxonomy
  links, encrypted historical animal snapshots, sighted/stolen/reunited
  states, immutable history, idempotent protected contact relay, advisory
  duplicate detection, reward-abuse controls, unified safety reports, and
  privacy-safe archive preservation.
- Extended the existing knowledge base into collaborative guides with
  independent review states, normalized collaborators, immutable versions and
  workflow events, optimistic editing, correction review, editorial locks,
  rollback-as-new-version, locale/taxon/jurisdiction scope, print/export, and
  an authorized class-based Livewire editor and administration registry.
- Added assigned low-risk community review panels and contextual notes with
  trust-based eligibility, conflict-aware reviewer balancing, reasoned
  one-reviewer decisions, deadline enforcement, replacement, appeals,
  append-only versions/events, author responses, moderator outcomes,
  revalidation, localized Livewire presentation, and a strict boundary to
  high-risk human moderation.
- Added opt-in peer mentorship for thirteen scopes with transparent bounded
  matching, independent professional-verification display, participant-only
  private threads, block/report safety, optional immutable feedback,
  optimistic lifecycle transitions, independently validated reputation,
  repeatable demo data, and class-based localized Livewire interfaces.
- Added persistent forum groups with public, request-to-join, private, and
  unlisted visibility; six roles; reviewed membership; expiring invitations;
  restriction, ownership, and lifecycle Actions; append-only events; unified
  reports; taxonomy focus; deterministic definitions; and localized
  class-based Livewire directory, workspace, and management interfaces.
- Added durable group topic/guide associations, events, announcements, private
  files, and single-, multiple-, and ranked-choice polls with configurable
  voter/result visibility, trusted/location eligibility, timestamp-derived
  closure, database-enforced one-vote projections, safe editable votes,
  localized non-authority notices, repeatable demo data, and accessible
  class-based Livewire ballots.
- Added typed forum progress journals for training, behavior, recovery,
  weight, rehabilitation, adoption adaptation, foster, aquarium, terrarium,
  pregnancy/newborn, and senior care. Journals retain the forum topic shell
  while adding dated entries, normalized measurements, milestones, setbacks,
  selected collaborators, comments, optimistic edit history, private
  content-validated images, bounded progress presentation, protected JSON
  export, archive, and idempotent legacy backfill. The private operational
  care journal remains a separate domain, and no streak, shame, reputation, or
  professional-authority side effect was introduced.
- Replaced authoritative session-only event mutations with normalized events,
  registrations, invitations, updates, attendee messages, reviews, immutable
  history, global taxon links, club links, encrypted access details,
  transactional capacity/waitlist handling, independent organizer
  verification, unified reports, stable-key backfill, and accessible
  class-based Livewire directory and workspace flows. Retired event presenters
  and styles were removed while legacy create/report URLs remain compatible.
- Added credential-backed professional question sessions with exact scope and
  jurisdiction eligibility, timestamp-derived question windows, private
  moderated queues, idempotent answers, safe source links, immutable
  corrections, non-destructive archives, unified reports, localized
  medical/legal non-authority notices, repeatable demo data, and accessible
  class-based Livewire directory and workspace flows.
- Added a durable forum-topic lifecycle with canonical and legacy-compatible
  states, optimistic and row locking, immutable transition history,
  category-specific stale and necropost projection, reversible removal,
  update requests and proposals, controlled bumping, merge and redirect
  preservation, encrypted legal holds, production-safe backfill, and an
  authorized class-based Livewire management panel.
- Completed the forum accessibility source contract with reusable focusable
  error summaries and linked fields, semantic table captions, meaningful
  image/video descriptions, required escaped video transcripts, optional
  content-validated WebVTT captions, localized legacy-media fallbacks,
  44-pixel controls, contrast/reflow guards, textual map alternatives, and a
  repeatable dependency-free Chrome smoke runner.
- Completed the forum multilingual-behavior contract with reviewed EN/LT/RU
  root-category descriptions, moderation definitions, animal-community
  groups, recipient-locale notifications, source-preserving human guide
  translations, private-source authorization, and verified common-name to
  exact-scientific-name taxonomy fallback.
- Corrected profile-locale updates to reload the complete document so the
  shell, title, navigation, and `html` language change together, and made the
  isolated fresh-database verifier enter testing mode before application
  bootstrap.
- Added an evidence overlay that prevents requirement verification without
  concrete file or test evidence; 1,140 atomic requirements are currently
  verified.

### Runtime And Architecture

- Researched current pet-community onboarding, discovery, settings, privacy,
  alert, and safety patterns from official product sources, then delivered a
  guest-only EN/LT/RU joining experience at `/` with privacy-aware product
  proof, metadata, language switching, zero guest application queries, and
  state-aware member routing to verification or the canonical content feed.
  The old fictional member feed is no longer guest-visible and remains only
  as an authenticated compatibility preview while its tested consumers exist.
- Added one dependency-ordered completion roadmap for all 29,960 combined
  forum/product requirements and synchronized the final audit to 1,140
  verified and 28,820 open atomic records.
- Raised the runtime contract to PHP `>=8.5.0 <8.6.0`, Laravel `^13.0`,
  Livewire `^4.3.4`, Tailwind/Vite plugin `^4.3.3`, and Vite `^8.2.0`.
- Updated the Laravel Boost Livewire skill while keeping canonical repository
  instructions in `AGENTS.md` instead of duplicating generated guideline
  blocks across agent entry points.
- Added Larastan 3.10 at PHPStan level 5 and resolved all reported
  first-party findings without a broad baseline.
- Added production authentication around the existing actor-key ownership
  model, class-based multi-file Livewire auth flows, active-account middleware,
  signed verification, reset, rate limiting, and policy enforcement.
- Rebuilt every authentication form around one responsive Blade component
  system with a distinctive split desktop composition, compact mobile flow,
  localized account context, explicit field semantics, 44-pixel actions, and
  shared offline/loading/validation states; language and time zone remain in
  protected profile settings rather than registration.
- Kept account-access links and post-auth redirects on full-document
  navigation so registration reaches email verification without duplicate
  Vite preload warnings.
- Standardized 44-pixel mobile search controls and clear-filter actions across
  lost/found, marketplace, and expert directories, plus forum category and
  filter links.
- Kept routes declarative, Blade passive, and persisted mutations in Actions or
  cohesive Services.
- Integrated Laravel 13's first-party image pipeline for marketplace,
  lost/found, sighting, and forum-topic photos with shared EXIF orientation,
  bounded resizing, WebP encoding, generated names, and localized failures.

### Security And Data

- Hardened the seven common web attack surfaces with fail-closed production
  session cookies, privacy-safe nested document lookup, realistic hostile-file
  validation, login/overposting regressions, and architecture guards against
  raw SQL, raw Blade, unfiltered request payloads, and client-named uploads.
- Removed the fixed prototype actor from protected operations and made the
  authenticated user's immutable actor key authoritative.
- Closed medical, care, device, order, booking, and coordination data to guests
  while retaining hashed, scoped, expiring temporary grants.
- Added identity fields through an additive migration and added leading indexes
  for 37 foreign keys across 25 existing tables.
- Added durable pet profiles, encrypted/versioned per-user social state,
  care-sync metadata, device retention/safety metadata, lifecycle records, and
  grouped device-event provenance through additive migrations.
- Added the canonical social-actor adapter, typed follow/friend/control graph,
  consent request lifecycle, immutable real-user audit, bounded compatibility
  backfill, and authenticated EN/LT/RU Livewire relationship center.
- Preserved private file authorization, encrypted sensitive values,
  idempotency, source provenance, and audit behavior in critical workflows.
- Prevented archived lost/found cases from appearing through directories,
  direct public URLs, or poster routes while retaining owner access and all
  sightings, updates, reports, identifiers, and events.
- Added fail-closed stolen/blocked-device policy while retaining owner-only
  lost-mode activation.
- Added baseline browser security headers and environment-gated demo accounts.

### Frontend And Localization

- Made eligible representative photographs, avatars, covers, and placeholders
  navigate to the same server-authorized internal destination as their titles
  across pets, groups, neighbors, meetups, discovery, profiles, experts,
  bookings, messages, and marketplace orders. Added an exhaustive 73-template
  classification while preserving gallery, viewer, current-page, QR, map,
  video, upload, action, and private-download semantics.
- Moved messaging folders out of the narrow inbox into a full-width responsive
  grid where every option stays visible without horizontal scrolling, retains
  a 44-pixel touch target, and leaves desktop scrolling to conversations only.
- Added one Laravel localization architecture for `en`, `lt`, and `ru` with
  validated persisted locale selection and English fallback.
- Extracted 2,340 Blade literals plus complete action, HTTP, Livewire, and
  service messages; dynamic sentences now use named placeholders and plural
  forms.
- Added locale-aware presentation formatting for user dates, times, numbers,
  percentages, currency, lists, measurements, and coordinates.
- Added automated guards against untranslated static/interpolated Blade text,
  `@php`, Volt, direct application calls in Blade, and unsafe environment reads.
- Added explicit Tailwind source detection, shared design tokens, visible
  focus, reduced-motion and forced-colors support.
- Normalized pet, group, neighbor, and event directories on one semantic
  result-grid contract with one mobile, two small-screen, and three
  wide-desktop columns plus matching responsive image sizes.
- Added one responsive PhotoSwipe publication viewer with progressive
  full-size-link fallback, localized zoom/swipe/keyboard controls, deep links,
  focus restoration, and per-photo authenticated reactions and escaped
  comments shared through indexed policy-authorized relational records.
- Corrected narrow-screen booking overflow and verified representative
  320-1920 px viewport behavior.

### Factories, Seeders, And Tests

- Kept Faker in production Composer installs because the environment-guarded
  `DatabaseSeeder` invokes model factories; fresh demo/test servers installed
  with `--no-dev` can now run `migrate:fresh --seed` without an undefined
  `fake()` helper.
- Added valid model factories for all 138 first-party Eloquent models and a
  complete automated factory/enum-state creation matrix.
- Made full demo seeding repeatable and production-safe.
- Added deterministic role/locale/privacy demo graphs and an opt-in
  production-blocked 250-profile performance seeder.
- Added an asserted temporary-SQLite fresh migration/seed verifier so
  destructive database checks cannot silently target the development file.
- Expanded Pest from a baseline of 116 tests / 3,881 assertions to the current
  checkpoint of 1,534 tests / 53,748 assertions.
- Added auth, authorization, localization, schema, factory/seeder,
  architecture, and responsive regression coverage.

### Documentation And Operations

- Established canonical requirements, architecture, domain/data/security,
  frontend, Livewire/Tailwind, accessibility, localization, testing, seeding,
  performance, deployment, operations, audit, ADR, and traceability documents.
- Catalogued 165 active requirements with evidence-backed status and generated
  a per-model seeding matrix.
- Documented deployment, forward-fix migration strategy, production seeding
  restrictions, provider boundaries, and the local-database audit incident.

### Verification

- Linked-media contract and affected regression slice passed 19 tests / 279
  assertions and 67 tests / 27,626 assertions respectively; the 24-case
  connected viewport matrix had no overflow, unnamed media links, nested
  interactive descendants, or console warnings/errors. The final serial Pest
  suite passed 2,303 tests and 76,111 assertions.
- Full Pest suite checkpoint: 1,534 passed, 53,748 assertions in serial mode.
- Larastan/PHPStan level 5: zero errors.
- Composer strict validation and security audit: passed, zero advisories.
- NPM high-severity audit: passed, zero vulnerabilities.
- Production Vite 8.2 build: passed.
- Fresh temporary SQLite migration and repeated seeding: passed.
- Config, route, and Blade cache compilation: passed.
- Coverage percentage remains unavailable because this PHP 8.5 runtime has no
  PCOV or Xdebug driver.

### Upgrade Notes

- Production requires PHP 8.5 and must run all pending additive migrations.
- Deploy both Composer and NPM lock files and rebuild frontend assets.
- Do not run demo seeders in production.
- New social mutations persist in encrypted per-user state; historical
  transient browser-session values were never production records and are not
  imported.
