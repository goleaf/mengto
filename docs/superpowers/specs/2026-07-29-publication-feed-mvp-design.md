# PawCircle Publication Feed MVP Design

## Status

Approved from the user's Point 2 product specification. This document narrows
the 98-section vision to an interactive Blade prototype that fits the current
session-backed architecture.

## Product Goal

Turn the existing home stream into the central PawCircle experience. The feed
must distinguish people, pets, groups, professionals, shelters, and urgent
animal notices while explaining why recommended content appears.

The MVP must prove these product contracts:

- Mia, Scout, and Nori can publish as separate identities.
- Feed mode, ordering, content type, and pet interest are explicit URL state.
- Recommended and chronological streams are visibly different.
- A post carries its audience, topic, safe place, media description, and
  comment policy.
- Reactions, comments, replies, bookmarks, reposts, subscriptions, hiding,
  reporting, archiving, and deletion change server-side prototype state.
- Privacy-sensitive and urgent formats receive distinct language and controls.
- Returning from a post thread can target the original feed card.

## Scope Boundary

This is a functional prototype, not a production social graph or media
pipeline. State remains in Laravel session storage. Curated media presets stand
in for real uploads so every control has an honest result without pretending
that files were stored, transcoded, scanned, or moderated.

Deferred production systems:

- database models, policies, indexes, cursor pagination, and background jobs;
- image upload, metadata stripping, transcoding, captions, and moderation;
- real recommendation ranking, anti-abuse signals, and notification delivery;
- stories, full-screen short video, translation, scheduling, analytics, ads,
  appeals, and organization administration.

## URL Contract

The canonical feed remains `/` and accepts validated query parameters:

| Parameter | Values |
| --- | --- |
| `feed` | `home`, `following`, `friends`, `pets`, `local`, `groups`, `experts`, `shelters`, `alerts`, `video`, `photos`, `saved`, `drafts`, `archive` |
| `sort` | `recommended`, `latest` |
| `type` | `all`, `text`, `photo`, `video`, `question`, `poll`, `event`, `lost`, `adoption`, `expert`, `group`, `repost` |
| `pet` | `all`, `dogs`, `cats`, `scout`, `nori` |
| `page` | positive integer capped by the presenter |

Filters stay shareable and survive navigation. Post threads link back to the
canonical feed plus `#post-{key}`.

## Service Boundaries

### `PawCircleFeedCatalog`

Owns immutable sample stories, posts, identity metadata, topics, media presets,
and recommendation explanations. It performs no session reads and no routing.

### `PawCircleFeedPresenter`

Combines catalog records with created posts and state. It filters, sorts,
paginates, decorates interactions, builds navigation options, and resolves
post/report/edit contexts.

### `PawCirclePrototypeState`

Owns mutable prototype records:

- created posts and drafts;
- post lifecycle status;
- selected reaction;
- comments and one-level replies;
- bookmarks, subscriptions, hidden posts, and muted authors;
- reposts and private reports.

### `PerformPawCircleAction`

Validates intent through `PerformPawCircleActionRequest`, delegates mutations to
state, and returns a named route plus feedback. Blade never contains business
logic or queries.

## Feed Composition

Desktop uses the existing three-column work-focused layout:

- left: compact owner card and identity-aware quick composer;
- center: feed controls, story rail, new-post notice, and stable-width stream;
- right: nearby events, urgent local notices, and recommendation tuning.

Mobile prioritizes author, represented profile, text, media, and the four most
common actions. Secondary safety and lifecycle commands live in a native
`details` menu. Horizontal mode rails use scroll snapping without widening the
page.

## Post Contract

Every post exposes a consistent shape:

- identity: author, represented profile, manager context, avatar, route;
- content: type, title, body, tags, topic, safe place, audience;
- media: zero or more responsive images, alt text, or native video metadata;
- context: recommendation reason, verification, urgent/sensitive state;
- interaction: reaction, counts, comments, bookmark, repost, subscription;
- ownership: editable, archivable, deletable, or reportable controls.

Text posts omit the media region. Multi-image posts use a horizontally
scrollable, keyboard-accessible media strip with stable aspect ratios. Urgent
lost-pet and adoption posts use explicit labels and safety guidance.

## Composer Contract

The post composer supports:

- publish as Mia, Scout, or Nori;
- post type, title, body, topic, hashtags, audience, and comment policy;
- safe public place rather than exact coordinates;
- curated media preset and required alt text when media is selected;
- sensitive-content disclosure;
- publish or save draft;
- editing a post owned by the current prototype user.

Validation treats all public state as untrusted. HTML is never accepted or
rendered.

## Interaction Rules

- One selected reaction per user and post; selecting another replaces it.
- Comments can have one reply level. Further replies stay at that level.
- Reposts reference the original; they do not clone private content.
- Hide removes a post from normal feeds but remains reversible in state.
- Archive is owner-only and reversible.
- Delete is owner-only and removes the post from public/session listings.
- Reports are private and do not expose the reporter.
- Sensitive and memorial contexts restrict inappropriate reaction choices.

## Accessibility And Performance

- One `h1`, semantic lists/articles, labelled controls, visible focus, and skip
  navigation.
- All media has explicit dimensions and useful alt text.
- No autoplay. Video uses native controls and `preload="metadata"`.
- Mobile targets are at least 40 px high.
- Text loads without waiting for media; responsive image sources remain lazy
  below the first viewport.
- Pagination is intentionally finite in the prototype and clearly marks the end.

## Verification

No PHP test files will be created or modified. Acceptance uses:

- Pint, PHP syntax, Blade cache, Vite build, and diff checks;
- component reference and layer audits;
- scans for Blade queries, raw SQL, and legacy component namespace;
- Playwright at 320, 375, 768, 1024, and 1440 px;
- reversible workflows for publish/draft/edit/react/comment/reply/save/repost/
  hide/report/archive/delete;
- image, keyboard, overflow, clipping, duplicate ID, and console checks.

## Implemented Prototype

The July 29 prototype implements the service boundaries, URL modes, composer,
reversible session actions, shared Blade components, native video, responsive
SCSS, comment replies, and safety states described above. Production-only
storage and delivery systems remain explicitly deferred in `docs/DESIGN.md`.
