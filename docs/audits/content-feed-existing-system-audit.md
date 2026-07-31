# Content Feed Existing System Audit

Date: 2026-07-31

## Current Presentation Layer

The home feed and composer are server-rendered Blade routes. `FeedCatalog`
provides a fixed preview catalogue, `FeedPresenter` combines that catalogue
with per-user prototype choices, and `PerformAction` dispatches generic feed,
comment, reaction, repost, bookmark, mute, and block actions. This is useful
for validating interface language and navigation, but it is not a shared
content system.

The application already has EN/LT/RU catalogues, reusable Blade components,
accessible controls, responsive layouts, and class-based Livewire 4 patterns.
Those presentation conventions can be reused by a query-backed feed without
putting models, authorization, or business rules in Blade.

## Existing Persistent Content

Several domains already own durable content and remain authoritative:

- forum topics, comments, guides, journals, group content, events, and polls;
- lost/found search cases, sightings, and status history;
- adoption cases and marketplace listings;
- knowledge articles, versions, corrections, and expert credentials;
- `PhotoAsset`, `PhotoComment`, and `PhotoReaction` records;
- unified `ForumReport`, moderation case, appeal, and private evidence data.

These records must be linked to a canonical publication through typed,
authorized references. They must not be copied into a second mutable source of
truth. Changes such as an event cancellation, completed adoption, or reunited
pet must remain authoritative in the owning module and project into every
feed placement or repost.

## Prototype State Boundary

`PrototypeState` stores bounded arrays in encrypted `UserDomainState` through
`PersistentStateStore`. The state is private to one user and may contain demo
catalogue keys. It cannot establish any of the following shared facts:

- a canonical post, author, audience, or publication status;
- another user's comment, reaction, mention, repost, or consent;
- durable version history, moderation, attribution, or deletion propagation;
- viewer-aware visibility, counters, search, or recommendation projections.

The legacy namespace must stay readable and intact. It may be used for a
bounded compatibility report or deterministic import of the current user's
own draft preferences, but it is never proof of cross-account publication or
interaction.

## Photo Interaction Boundary

The photo interaction tables already provide persistent user attribution,
idempotent comment submission, and one reaction per asset and user. Their
`post_key` is a string compatibility reference rather than a foreign key to a
canonical publication. Policies cover authenticated access and ownership of
mutations, but no post audience, represented publishing profile, repost,
mention, or content-lifecycle aggregate exists.

The canonical content foundation may adapt existing photo rows without
deleting them. A migration must preserve IDs, comments, reactions, storage
paths, and old viewer URLs while establishing an explicit authorized relation
to new media and publication records.

## Identity, Authorization, And Moderation

`SocialActor` is the canonical adapter for a user, pet, expert, or supported
organization profile. A publication therefore needs both a represented
publishing actor and the real authenticated `User` who created or changed it.
Social friendship or following can affect audience membership, but never
grants pet administration, medical access, device control, or media rights.

The existing unified report and moderation pipeline already supports private
evidence, cases, actions, appeals, and reporter protection. Content-specific
reports and moderation events should extend that pipeline instead of creating
a parallel moderation system.

## Query And Schema Baseline

There is no canonical publication, audience rule, media lifecycle, draft,
version, story, repost, mention, hashtag, bookmark collection, feed placement,
or content notification table. Consequently the current preview has no
production query-count baseline: it reads a fixed catalogue plus one encrypted
state row.

The first production package requires additive indexed tables and bounded
Eloquent projections. It must avoid raw SQL, unbounded model reads, Blade
queries, aggregate calls in loops, and public Livewire payloads containing a
feed graph or private audience membership.

## Runtime Constraints

The repository is a workerless server-rendered web application with no
websocket, external search, native mobile, Filament, React, Vue, Inertia, or
Flux dependency. Initial media processing therefore uses explicit resumable
web transitions and observable statuses. Native background upload, external
AI, live video, and asynchronous transcoding require later infrastructure
decisions and cannot be claimed by the foundation.
