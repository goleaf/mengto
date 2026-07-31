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
