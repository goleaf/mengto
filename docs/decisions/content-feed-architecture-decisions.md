# Content Feed Architecture Decisions

Date: 2026-07-31

## ADR-CONTENT-001: One Canonical Publication

Use one `ContentPublication` aggregate as the original material. Store the
represented `SocialActor`, the real authenticated author, type, lifecycle,
locale, publication time, optimistic version, and interaction capabilities.
Profile, group, event, hashtag, search, and feed views are authorized
projections or typed placements, not physical copies of the post.

## ADR-CONTENT-002: Audience Is A Read-Time Rule

Represent audience as a structured rule with inclusions, exclusions, context,
and optional expiry. Every read rechecks publication status, profile privacy,
current social relationships, group/event membership, blocks, account state,
and specific exclusions. The effective audience can never be broader than an
authoritative parent context without explicit confirmation.

## ADR-CONTENT-003: Distribution Cannot Widen The Original

Reposts, bookmarks, mentions, hashtag pages, recommendations, external
previews, and direct URLs reference the original publication. They cannot make
an unavailable original visible. Original edits, privacy contraction,
moderation, expiry, or deletion must propagate to every projection.

## ADR-CONTENT-004: Media Has An Independent Lifecycle

Use canonical media records for ownership, creator attribution, licence,
original and derivative objects, processing state, privacy, metadata policy,
alt text, captions, moderation, and retention. Publication-media relations
carry order, cover selection, and contextual captions. Private media uses
short-lived signed delivery or a policy-checked response, never a permanent
public URL.

## ADR-CONTENT-005: Existing Domains Remain Authoritative

Forum topics, groups, events, lost/found operations, adoption cases,
marketplace listings, care records, and knowledge articles retain ownership of
their business state. A publication stores a typed authorized link and renders
the current allowed projection. It does not duplicate an event date, adoption
status, exact search location, medical record, or transaction state.

## ADR-CONTENT-006: Prototype State Is Compatibility Data

`UserDomainState` feed and composer namespaces remain readable and are not
deleted. Demo keys and personal arrays are not consent or shared facts. Any
import is bounded, idempotent, user-owned, auditable, and leaves ambiguous
items untouched.

## ADR-CONTENT-007: Reuse Unified Moderation

Content, comments, mentions, media, and copyright reports extend `ForumReport`
and its existing case, action, evidence, appeal, and recusal boundaries.
Content-specific events may project moderation state, but no parallel report
identity or reporter disclosure path is introduced.

## ADR-CONTENT-008: Workerless Transitions Are Explicit

The initial server-rendered implementation uses resumable chunks and explicit
upload, validation, processing, ready, failed, and cancelled statuses.
Expensive transcoding, live delivery, AI, and native background guarantees are
enabled only after an approved provider/runtime package exists. Until then,
the interface reports the real synchronous or deferred state.

## ADR-CONTENT-009: Feed Queries Are Bounded Projections

Feed services select explicit columns, eager-load required relationships, and
use cursor pagination with stable indexed ordering. Authorization is applied
before a row enters the projection. Counters are preloaded with Eloquent
aggregates and adjusted for viewer privacy; Blade never queries or computes a
domain decision.
