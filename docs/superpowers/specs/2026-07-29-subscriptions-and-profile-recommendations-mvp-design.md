# PawCircle Subscriptions And Profile Recommendations MVP Design

## Status

Approved from the user's Point 3 product specification. This document narrows
the 109-section vision to an interactive Blade prototype that extends the
existing owner, pet, organization, and feed identities.

## Product Goal

Give people direct control over whose content enters their feed while keeping
owner, pet, organization, and topic relationships independent. Recommendations
must explain why they appear and must respond immediately to follow, mute,
favorite, block, and not-interested signals.

The MVP proves these contracts:

- following a person never follows all of their pets implicitly;
- a pet, owner, specialist, shelter, group, and topic have distinct targets;
- public profiles follow immediately while private profiles create requests;
- each subscription has a visible notification level and feed state;
- a chronological Following feed reflects active, unmuted subscriptions;
- recommendation cards always include a human-readable reason;
- dismissed and blocked targets stop appearing in recommendations;
- the user can undo a recommendation dismissal;
- follower lists expose only prototype-safe public fields.

## Scope Boundary

This is a functional session-backed prototype. It does not claim to implement a
production social graph, delivery service, abuse classifier, or ranking model.

Deferred production systems:

- Eloquent social-graph models, policies, indexes, counters, and cache
  invalidation;
- push, email, browser, SMS, quiet-hours, and digest delivery;
- contact import, referral matching, and encrypted identifier discovery;
- anti-bot rate limits, fake-follower analysis, moderation queues, and appeals;
- machine-learned recommendation ranking and analytics;
- family/minor safety enforcement and organization tenancy.

## URL Contract

The relationship center uses `/circle/connections` with validated query state:

| Parameter | Values |
| --- | --- |
| `tab` | `following`, `followers`, `requests`, `recommendations` |
| `type` | `all`, `people`, `pets`, `organizations`, `specialists`, `groups`, `topics` |
| `sort` | `recommended`, `recent`, `name` |

All tabs remain server-rendered and shareable. The feed keeps
`/?feed=following&sort=latest` as the explicit recommendation-free stream.

## Service Boundaries

### `ConnectionCatalog`

Owns immutable profile, topic, follower, request, and recommendation records.
Every target has a stable key, type, public route, image, safe location,
verification state, and recommendation explanation.

### `ConnectionPresenter`

Combines catalog records with session state, applies filters and sorting,
decorates actions, computes counts, and hides blocked or dismissed records.
Blade receives display-ready arrays only.

### `PrototypeState`

Owns mutable relationship state:

- subscription status by exact target;
- private-profile request status;
- notification level;
- favorite and muted state;
- follower removal;
- recommendation dismissal and undo history;
- blocked targets.

### `PerformAction`

Receives only allow-listed actions and target keys. It validates target
existence through the connection presenter, mutates state, and redirects with
plain feedback.

## Relationship Model

The prototype uses exact namespaced targets such as `owner-ari-jensen`,
`pet-mochi`, `organization-rose-city`, `specialist-elena-ruiz`,
`group-apartment-pets`, and `topic-positive-training`.

Public targets toggle between `Follow` and `Following`. A private target moves
from `Follow` to `Request sent`; cancelling returns it to the initial state.
Friendship remains a separate existing action and is never inferred.

Notification levels are `all`, `important`, `standard`, `feed`, and `off`.
Muting preserves the relationship while removing ordinary feed content.
Favorite subscriptions receive a visible priority marker.

## Screen Composition

The relationship center uses one compact page header and one tabbed workspace:

- summary strip for following, followers, pending requests, and favorites;
- URL-driven type and sort controls;
- dense list rows for managed subscriptions and requests;
- responsive recommendation grid grouped by local, pet, expert, and community;
- reason labels and privacy/verification badges;
- immediate follow, favorite, mute, settings, dismiss, and undo actions.

Desktop uses a constrained two-column workspace where useful. Mobile collapses
to one column, keeps the primary action visible, and places secondary settings
in a native details menu. Cards are not nested inside decorative cards.

## Feed Integration

The Following feed includes only posts whose author or represented identity has
an active subscription. Muted and blocked identities are excluded. Following
an owner does not include unrelated pet-authored posts, and following a pet does
not include unrelated owner-authored posts.

The curated prototype starts with several active subscriptions so the stream is
useful before interaction. New follows update the relationship center and the
Following feed in the same session.

## Accessibility And Safety

- Follow state is expressed through text and `aria-pressed`, not color alone.
- Buttons keep at least a 44 px mobile target.
- Recommendation reasons remain visible text.
- Private recommendations expose only public-safe profile data.
- Dismiss actions provide a reversible undo control.
- Blocked targets are excluded from recommendations and subscription actions.
- Exact home distance, medical data, contacts, and private posts are absent.

## Verification

No PHP test files will be created or modified. Acceptance uses:

- Pint, PHP syntax, Blade cache, Vite build, route inspection, and diff checks;
- scans for raw SQL, Blade queries, and the removed `x-pet-social` namespace;
- HTTP checks for every tab/filter and invalid input;
- Playwright at 320, 375, 768, 1024, and 1440 px;
- follow/unfollow, private request/cancel, favorite, mute, notification,
  dismiss/undo, remove follower, block, and Following-feed workflows;
- overflow, image, duplicate ID, focus, target-size, and console checks.
