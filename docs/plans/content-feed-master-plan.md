# Content Feed Master Plan

Date: 2026-07-31

Status: incomplete; 58 content IDs are verified and 3,953 remain open.

## Contract

The content-feed revision contributes 4,011 stable `content.*` requirements to
the immutable combined specification. Existing requirement identifiers remain
unchanged. Every phase selects exact IDs from
`docs/plans/forum-phase-requirement-index.md`, and no requirement is verified
without implementation, tests, and evidence.

## Phase 35: Preservation, Audit, And Design

Preserve the source verbatim, generate atomic requirements, inventory existing
content and prototype state, record assumptions/conflicts/ADRs, and define the
phase/evidence contract. No production schema or runtime capability is claimed.

## Phase 36: Publication, Audience, And Data Foundation

Introduce the canonical publication, represented and real author attribution,
statuses, structured audience rules, interaction capabilities, typed domain
links, media records and relations, immutable events, policies, scopes,
factories, compatibility report, and one bounded chronological projection.

Migration is additive. Existing forum, photo, search, adoption, event,
marketplace, knowledge, and encrypted prototype records remain intact. Any
backfill is deterministic, chunked, idempotent, and separately reversible.

Acceptance includes fresh migration and rollback, index and foreign-key
inspection, positive/negative audience policy matrices, block and expiry
tests, attribution and idempotency tests, query budgets, factory creation,
EN/LT/RU labels, and proof that no private object leaks through direct URLs.

## Phase 37: Authoring And Lifecycle

Add typed editors, drafts, autosave, upload queue, scheduling, time zones,
approval, collaboration, optimistic conflict handling, preview, version
history, pin, archive, deletion, trash recovery, rights transfer, media
attribution, and copyright complaints.

## Phase 38: Stories And Social Distribution

Implement stories and archives, reactions, nested comments, comment controls,
mentions and approval, hashtags and follows, internal reposts, bookmarks and
collections, grouped notifications, and home/following/friends/group/local/
urgent/profile/chronological projections. Every mutation is idempotent and
retains the real authenticated actor.

## Phase 39: Media Processing And Delivery

Add image orientation and metadata removal, redaction support, alternatives,
video/audio validation, derivatives, captions/transcripts, data-saving and
autoplay preferences, resumable transfer, private signed delivery, licence,
retention, and backup/restore behavior. Live streaming stays deferred until a
separate runtime decision is approved.

## Phase 40: Feed Quality, Search, Analytics, And Notifications

Add transparent ranking factors, chronological escape hatch, explanation and
control surfaces, author/type diversity, freshness, urgent updates, viewed
state, stable resume position, search indexing boundaries, external indexing
controls, author analytics, safety/privacy metrics, and guarded experiments.

## Phase 41: Safety And Moderation

Integrate location and minor protection, doxxing/phishing/spam/fraud checks,
cruelty and dangerous-advice handling, fundraising and trade risk, coordinated
harassment protection, structured reports, temporary hiding, proportional
actions, evidence retention, explanations, appeals, copyright, and false-report
resistance with the unified moderation pipeline.

## Phase 42: Domain Integrations And Optional AI

Complete typed projections for pet profiles, care and medical summaries,
lost/found, adoption, groups, events, marketplace, experts, devices, and
places. Add AI assistance only behind explicit consent and provider controls;
AI output remains a marked draft and cannot invent pet facts or decide final
high-risk moderation alone.

## Phase 43: Localization, Accessibility, And Resilient Interface

Complete original-preserving translation, protected terms, multilingual
versions and comments, semantic post structure, keyboard operation,
captions/transcripts, reduced motion, large-text reflow, non-colour statuses,
mobile editor ergonomics, local draft recovery, resumable upload, and honest
weak-network states in EN/LT/RU.

## Phase 44: Release And Scenario Verification

Verify the first-release type/feed/interaction/privacy/moderation/technical
contracts and all ideal scenarios. Run fresh migration, repeated seed, full
serial suite, Pint, Larastan, Composer/NPM audits, production build, cache
smoke, desktop/mobile/320px browser checks, accessibility inspection, complete
traceability audit, staged diff review, commit, and push.

## Global Invariants

- one original publication, with all placements and reposts referencing it;
- represented profile and real user actor are always distinguishable;
- audience and blocks are checked at every read and media delivery;
- a child context or repost cannot widen the original audience;
- authoritative domain status is linked, never copied as a competing truth;
- privacy contraction and moderation invalidate every projection promptly;
- no query or business decision occurs in Blade;
- public Livewire state is small, typed, browser-visible, and untrusted;
- no exact location, medical record, document, or minor identity enters an
  ordinary feed payload;
- unfinished requirements remain open with no optimistic completion claim.
