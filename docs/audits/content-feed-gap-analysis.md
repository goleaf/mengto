# Content Feed Gap Analysis

Date: 2026-07-31

## Reusable Foundations

- canonical users, pets, experts, organizations, groups, events, and social
  actors;
- persistent forum, lost/found, adoption, marketplace, and knowledge records;
- private media delivery and content-validated uploads;
- persistent photo comments and reactions;
- unified reports, moderation cases, appeals, and evidence;
- EN/LT/RU Blade and class-based Livewire conventions;
- cache namespace invalidation, policies, factories, and architecture tests.

## Critical Missing Capabilities

1. There is no canonical publication with real-user and represented-profile
   attribution.
2. There is no structured audience rule checked against current relationships,
   membership, blocks, exclusions, and expiry on every read.
3. Media has no independent processing, privacy, derivative, licence, alt-text,
   caption, or retention lifecycle shared across publications.
4. Drafts, autosave, scheduling, approval, versions, archive, trash, and
   concurrent editing are not durable workflows.
5. Stories, polls, questions, mentions, hashtags, reposts, bookmarks, and
   notifications lack canonical objects and idempotent transitions.
6. Home, following, friends, group, local, urgent, profile, and chronological
   feeds are not query-backed viewer-aware projections.
7. Privacy contraction cannot yet invalidate every feed, search, hashtag,
   repost, external preview, cache, and media URL.
8. Content safety rules for location, minors, doxxing, phishing, cruelty,
   dangerous advice, fundraising, spam, copyright, and coordinated harassment
   are not integrated with publication lifecycle.
9. Feed ranking, explanations, controls, analytics, AI assistance, and external
   indexing have no production data boundary.

## First Safe Production Package

Phase 36 establishes the minimum reusable aggregate: publication identity and
status, represented and real actor attribution, structured audience rules,
interaction capabilities, typed links to authoritative domains, independent
media records, immutable events, policies, factories, scopes, and compatibility
reporting. It also provides one bounded chronological profile/feed projection
to prove server-side visibility.

This package does not claim a complete editor, stories, recommendations,
advanced video processing, AI, native offline operation, or every specialized
post type. Those remain open in phases 37-44 and cannot receive verified
evidence from a foundation-only implementation.

## Main Risks

- **Privacy leakage:** mitigate with read-time policy evaluation, most
  restrictive parent context, explicit exclusions, blocks, signed private
  media delivery, and immediate cache namespace invalidation.
- **Source duplication:** link events, adoption, search, forum, and marketplace
  records as authoritative objects rather than copying mutable status.
- **False attribution:** store both represented actor and authenticated user in
  every mutation and append-only event.
- **Prototype over-import:** preserve encrypted legacy state and import only a
  user's deterministically attributable data after review.
- **Table explosion:** keep a small publication core with typed extension data
  and add specialized tables only when a required invariant needs them.
- **Race conditions:** use idempotency keys, optimistic versions, unique active
  keys, transactions, and row locks for publish and interaction transitions.
- **Workerless media:** expose resumable upload and processing states; do not
  report asynchronous transcoding or native background guarantees before the
  required infrastructure exists.
