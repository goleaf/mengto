# Community Master Plan

Date: 2026-08-01

## Contract

The exact Point 6 source is preserved in the combined master prompt. The
deterministic catalogue contains 3,576 stable `community.*` requirements in
phases 55-63. Earlier requirement IDs and ordering remain unchanged. An atom
is verified only when implementation, automated tests, documentation, and
traceability evidence agree.

## Phase 55: Foundation And Community Types

Complete canonical community identity, type templates, purpose, geography,
languages, profile participation, official/commercial disclosure, and the
relationship between parent communities, chapters, and subgroups.

## Phase 56: Creation, Privacy, And Membership

Implement draft/preview/publish, stable aliases, visibility and external
indexing, member-list privacy, join modes, applications, invitations, expiring
links and QR codes, waiting lists, trial state, exits, removals, and profile-
scoped membership. The current package establishes the profile membership
foundation only.

## Phase 57: Roles And Governance

Add capability-scoped fixed and custom roles, temporary grants, owner and
co-owner controls, administrator hardening, role audit, immutable rule
versions, localized rules, re-consent, advertising, fundraising, adoption,
lost/found, medical, and real-world event governance.

## Phase 58: Structured Forum

Complete community sections, subtopics, question types, duplicate detection,
accepted and expert answers, statuses, merge/split/move/close, pseudonymous
sensitive topics, and durable attribution.

## Phase 59: Knowledge Base

Build community curation over canonical knowledge articles: sources, review
levels, versions, freshness checks, correction proposals, FAQ, checklists,
templates, translation status, and promotion from forum discussions.

## Phase 60: Events, Volunteering, And Finance

Finish community event permissions, pet registration, capacity, waitlists,
check-in privacy, safety plans, incidents, recurring/virtual events, media
consent, task boards, shifts, skills, risk gates, reports, fundraising,
expenses, sponsorship, and conflict disclosure.

## Phase 61: Safety And Moderation

Integrate layered reports, temporary hiding, proportional actions, recusal,
explanations, appeals, repeat violations, coordinated harassment, raids,
block-evasion analysis, dangerous advice, cruelty, illegal trade, fraudulent
shelters/fundraisers, doxxing, minor safety, and sensitive-group privacy.

## Phase 62: Discovery, Localization, Interface, And Reputation

Add privacy-safe search/filter/recommendation explanations, sensitive-signal
exclusions, notification categories and digests, language sections and
protected translation fragments, keyboard/screen-reader/large-text/reduced-
motion coverage, mobile moderation, weak-network behavior, factual badges,
and non-harmful recognition.

## Phase 63: Lifecycle, Data, Quality, And Release

Complete lifecycle states, pause/read-only/archive, merge/split/transfer,
backup owner, inactivity, deletion grace period, privacy-safe export,
capability checks, idempotency, optimistic conflicts, cache/search invalidation,
retention, audit, community-health metrics, and all release scenarios.

## Global Invariants

- one canonical community identity and stable route key;
- real account and represented participation profile are always distinguishable;
- a profile may join once per community and only while currently controlled;
- role, membership, block, group state, and expiry are checked on the server;
- child modules and lifecycle transitions never widen private visibility;
- official domain facts are referenced, not copied into user text;
- Blade contains presentation only and receives bounded eager-loaded data;
- public Livewire properties are browser-visible, minimal, and untrusted;
- unfinished Point 6 atoms remain open without optimistic completion claims.
