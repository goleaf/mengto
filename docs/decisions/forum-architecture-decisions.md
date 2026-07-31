# Forum Architecture Decisions

## ADR-FORUM-001: Additive Normalization

**Decision:** Introduce normalized category, taxonomy, reputation, confirmation,
and moderation tables through new migrations. Existing topic category strings,
slugs, actor keys, answers, engagements, reports, and knowledge links remain
readable during the transition.

**Reason:** Existing records and URLs must survive. Expand-and-contract permits
backfill, verification, and rollback without deleting data.

## ADR-FORUM-002: Category Keys And Translations

**Decision:** Category identity is an immutable stable key. Slugs are routable
aliases, not identity. Names, descriptions, notices, and rules use stable
translation keys and locale catalogues. System and administrator categories
share one model but have distinct ownership metadata.

## ADR-FORUM-003: Taxonomy Source And Format

**Decision:** Catalogue of Life Base Release is the primary broad snapshot.
The application imports a local versioned Darwin Core Archive-compatible
snapshot using Darwin Core field semantics. Source metadata, version,
download date, checksum, license, attribution, and provenance are mandatory.
WoRMS and matching services are supplementary only after explicit source
registration.

**Reason:** Catalogue of Life publishes permanent annual snapshots and current
base releases. The Base Release prioritizes curated global sources; normal
requests must not depend on an external API.

## ADR-FORUM-004: Scientific And Domestic Classification

**Decision:** Scientific taxa, local/common names, external identifiers,
domestic classifications, registries, and community groups are separate
relations. An extensible string-backed rank registry is used instead of a
closed enum.

## ADR-FORUM-005: Import Activation

**Decision:** Imports are versioned, locked, chunked, and resumable. Imported
rows remain attached to an inactive import version until validation succeeds.
Activation changes the source's active import pointer atomically. Failed or
cancelled imports never become active.

## ADR-FORUM-006: Reputation And Trust

**Decision:** Reputation is an append-only scoped ledger with explicit
reversal events and aggregates. Trust level and professional verification are
separate audited domains. Public display never exposes humiliating negative
totals.

## ADR-FORUM-007: Reports And Cases

**Decision:** A report is a user allegation about one polymorphic subject. A
moderation case is the authorized investigation that may group reports.
Actions, appeals, recusal, and immutable events belong to the case. Private
evidence is stored separately and is never serialized publicly.

## ADR-FORUM-008: Server-Rendered Interaction

**Decision:** Existing public browsing remains Blade and ordinary links/forms.
Complex server-backed selectors, reports, consensus, ledgers, and
administration use normal class-based Livewire 4 components with separate
views. No Volt or additional frontend framework is introduced.

## ADR-FORUM-009: Search

**Decision:** Begin with bounded Eloquent/database search and the existing
deployment. Define a query boundary so an approved search adapter can be added
later. Private visibility is applied before text matching, counts, snippets,
or autocomplete.

## ADR-FORUM-010: Adjacent Domains

**Decision:** Existing `SearchCase`, expert, marketplace, pet, event/social,
and knowledge records remain authoritative. Forum topics link to them using
typed relations or structured references; they do not duplicate complete
workflows.

## ADR-FORUM-011: Adoption Provider Identity

**Decision:** Adoption provider identity reuses the independent expert
credential-review boundary. An adoption case links to a profile owned by the
listing provider and to a purpose-compatible credential. Private providers
require a current identity credential; organization providers require a
current organization-role or organization-registration credential. The legacy
marketplace seller flag is preserved but is not evidence of adoption-provider
identity.

**Reason:** Credential review already provides private evidence, independent
authorization, expiry, suspension, revocation, appeals, and audit history.
Creating a second adoption-only verification system would produce conflicting
trust states. A future organization-management module can become an additional
credential subject without changing the adoption verification contract.

## ADR-FORUM-012: Lost-And-Found Closure And Archival

**Decision:** `SearchCase.status` records the operational case outcome while
`archived_at` records privacy-safe removal from public discovery. Archival is
allowed only for an already closed case, preserves the case identifier and all
related sightings, relays, assignments, events, reports, and audit history, and
stops any remaining urgent processes. Owners and authorized coordinators may
still inspect the archived record; public access and active-case lookup do not
include it.

**Reason:** A terminal outcome such as reunited, returned, cancelled, or closed
is historical case data. Treating archival as another status would erase that
outcome and make preservation, restoration, and accountability ambiguous.

## ADR-FORUM-013: Lost-Animal Snapshot Compatibility

**Decision:** A search case stores a privacy-safe animal snapshot at creation
time. Temperament is a dedicated snapshot field. Collar and similar visible
identifiers use the existing structured `accessories` list instead of a second
collar column. Exact coordinates, contact details, medical notes, and identifying
notes remain encrypted; public projections expose only rounded or explicitly
public values.

**Reason:** The snapshot must remain useful when a linked pet profile later
changes while avoiding duplicate sources of truth and disclosure of private
owner or animal data.

## ADR-FORUM-014: Collaborative Guide History And Review Independence

**Decision:** Extend `KnowledgeArticle` as the single collaborative-guide
aggregate. Content snapshots and workflow events are append-only. Rollback is a
new version. Community and expert review are independent policy decisions;
administrator status cannot stand in for scoped community trust or current
professional verification. Locale variants are separate articles joined by a
stable translation-group key.

**Reason:** This preserves existing guide URLs and correction history while
making editing auditable and race-safe. It also prevents forum popularity,
general reputation, or administrative authority from being presented as
scientific or professional review.
## ADR-FORUM-015: Separate Forum Journals From Private Care Journals

Date: 2026-07-31

Status: accepted

`CareJournal` remains the private operational record for a managed pet,
including routines, tasks, medication-adjacent events, encrypted measurements,
temporary access grants, and private care files. A forum journal is public or
selectively shared community content and must not read, copy, or weaken that
care boundary.

Forum journals therefore use `ForumTopic` as their publication, category,
group, locale, moderation, and engagement shell and add normalized
journal-specific tables for entries, measurements, collaborators, and private
images. The two domains may reference the same pet only through separately
authorized context; they do not share entries or files.

## ADR-FORUM-016: Reuse Topic Comments With An Entry Relation

Date: 2026-07-31

Status: accepted

Journal entry comments reuse `ForumComment` through an additive nullable
`forum_journal_entry_id`. Existing answer comments retain their current path.
The journal comment Action requires exactly one journal entry, reloads it
under the topic and journal, and writes an idempotency key. This avoids a
second incompatible community-comment model while preserving existing rows.

## ADR-FORUM-017: Keep Journal Privacy Canonical On The Topic

Date: 2026-07-31

Status: accepted

`forum_topics.visibility` remains the single privacy value for a journal.
Journal policies evaluate that value together with owner, selected
collaborator, group-membership, and active-account context before querying
entry, comment, measurement, image, or export data. The journal table does not
duplicate privacy state.

Member, expert, link, group, and private topics are excluded from anonymous
directory queries. Direct access is evaluated by policy. This closes the
existing risk in which every non-private visibility value behaved as public.

## ADR-FORUM-018: Use Normalized Measurements And Native Progress Semantics

Date: 2026-07-31

Status: accepted

Queryable numeric measurements use a normalized table and a typed
journal-type metric registry with canonical units and bounds. Narrative
context remains in the entry body. The first interface renders bounded
server-prepared history through semantic tables, textual values, and native
`progress` elements; no chart framework or unbounded browser payload is
introduced.

## ADR-FORUM-019: Normalize Platform Events And Reuse Groups As Clubs

Date: 2026-07-31

Status: accepted

`ForumEvent` is the canonical platform event aggregate. A club is an existing
`ForumGroup`; an event can belong to a group without creating a second club
identity. Existing `ForumGroupActivity` rows remain as compatibility
projections and gain a nullable canonical event relation. New group
activities create their canonical event in the same transaction.

The old `EventCatalog` keys are preserved through an idempotent additive
backfill. New event mutations write normalized tables rather than the
user-scoped session snapshot. The old snapshot may be inspected for migration
evidence but cannot decide global capacity, authorization, or attendance.

## ADR-FORUM-020: Derive Organizer Verification From Credentials

Date: 2026-07-31

Status: accepted

An event never stores a self-asserted or reputation-derived verified-organizer
boolean. Public verification is a current projection of the organizer's
published expert profile and independently reviewed, unexpired credential
state. Expiry, suspension, or revocation therefore changes the displayed
state without rewriting event history.

An organizer name snapshot preserves historical attribution when an account
is unavailable. It grants no management ability and cannot display as
verified.

## ADR-FORUM-021: Protect Event Access Details And Emergency Plans

Date: 2026-07-31

Status: accepted

Public location scope, accessibility information, attendance rules, animal
welfare rules, cost, and refund terms are presentation data. Exact physical
locations, online access links, participant requirements notes, and emergency
contact plans are encrypted and hidden from serialization. Policies disclose
exact access details only to the organizer, an authorized administrator, or
an eligible confirmed attendee.

Event vaccination text is an organizer's lawful attendance requirement, not
medical proof. This package does not collect or publish participant
vaccination documents.

## ADR-FORUM-022: Use Database Capacity And Waitlist Invariants

Date: 2026-07-31

Status: accepted

One event and user can have only one registration row. Registration,
cancellation, approval, and waitlist promotion lock the canonical event row,
recalculate seat use inside a short transaction, and append history. Unique
constraints protect idempotency, registrations, invitations, and reviews.

Paid-event amounts are stored as integer minor units with an ISO currency and
visible refund terms. No payment credentials or successful payment state are
invented while the repository has no real payment-provider boundary.

## ADR-FORUM-023: Separate Expert Sessions From Advice And Appointments

Date: 2026-07-31

Status: accepted

`ForumExpertSession` is a scheduled, public community-question aggregate. It
does not represent a private consultation, veterinary examination, patient
relationship, prescription, diagnosis, legal engagement, or formal legal
advice. Ordinary `ForumTopic`, `ForumAnswer`, `Consultation`, and
`KnowledgeArticle` records remain authoritative in their existing domains.

A session displays a versioned localized disclaimer and records professional
scope and jurisdiction as context. Community reactions, acceptance, karma,
trust, badges, and administrative status cannot elevate an answer into a
medical or legal conclusion.

## ADR-FORUM-024: Derive Host Eligibility From Current Credentials

Date: 2026-07-31

Status: accepted

Hosting and answering require an active owner of a published, current,
independently verified `ExpertProfile` and a current compatible reviewed
credential. The session stores host name, scope, and jurisdiction snapshots
for historical attribution, but rechecks current eligibility for every
professional mutation.

Professional scope remains an extensible stable key. Known keys receive
localized labels, while the credential and profile retain the authoritative
identifier. Credential evidence, identifiers, notes, and reviewer metadata
never enter public or Livewire state.

## ADR-FORUM-025: Derive Session Windows Without Runtime Workers

Date: 2026-07-31

Status: accepted

Question-window and live-session phases are derived from validated timestamps.
Archival is an explicit audited action. Voting and submission boundaries
therefore remain correct without cron, scheduler, queue worker, websocket
server, or another operational dependency.

Questions, answers, corrections, moderation decisions, and history are
normalized and database constrained. Source links are displayed as validated
HTTP(S) references and are never fetched by the application.

## ADR-FORUM-026: Use An Audited Topic Lifecycle State Machine

Date: 2026-07-31

Status: accepted

Topic status changes pass through one domain service that validates an
explicit transition graph, locks the row, checks an optimistic version, updates
state timestamps, and appends immutable history. Answer creation, answer
acceptance, owner actions, and moderator actions use the same boundary.

Legacy status values remain readable during migration, but new mutations write
canonical values. Derived facts such as "unanswered" remain query projections
instead of contradictory persisted lifecycle states.

## ADR-FORUM-027: Preserve Removed And Old Topics

Date: 2026-07-31

Status: accepted

User deletion becomes a reversible `removed` transition. Archive, removal,
merge, redirect, and restore preserve the topic ID, slug, text, replies,
reactions, subscriptions, bookmarks, reports, attachments, moderation history,
and taxonomy relations. Public visibility is controlled by policy and scopes.

Legal holds are separate private audit records. An active hold blocks sensitive
lifecycle transitions and destructive maintenance. No age-based process
physically deletes topic content.

## ADR-FORUM-028: Derive Staleness At Read Time

Date: 2026-07-31

Status: accepted

Category lifecycle rules define stale, necropost, archive-review, retention,
reopen, and bump thresholds. A bounded projection derives warnings and
effective read state from stored timestamps. GET requests never mutate topic
state, and correctness does not depend on cron, a queue worker, or a long-lived
runtime.

Archive and removal remain explicit authorized actions. A retention deadline
creates a review signal, not an automatic deletion command.
