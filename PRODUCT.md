# Product

## Register

product

## Users

PawCircle is for pet owners who want useful, low-pressure connections with nearby people and their animals. They use it on a phone while planning walks or checking local activity, and on larger screens when creating profiles, groups, and meetups.

## Product Purpose

PawCircle brings neighborhood pet discovery, practical care context, post conversations, walk planning, meetups, groups, personal collections, notifications, and direct messages into one calm social workspace. Success means a person can understand who and what is nearby, take a clear next action, and return later without losing context.

## Brand Personality

Calm, neighborly, practical. The product should feel welcoming without becoming childish, trustworthy without becoming clinical, and social without pushing engagement for its own sake.

## Anti-references

- Engagement-heavy social feeds that prioritize counters, urgency, or infinite consumption.
- Marketing-style hero layouts, decorative dashboard cards, purple gradients, glass effects, and oversized rounded surfaces.
- Infantilized pet branding, novelty controls, and decoration that competes with real animals and useful local information.
- Tiny controls, low-contrast helper text, or color-only states that make outdoor and mobile use harder.

## Design Principles

- Put the next useful neighborhood action within one clear tap.
- Let real pets, people, places, and routines carry the visual character.
- Keep interaction patterns familiar and consistent across every directory and detail page.
- Preserve context through URLs, visible state, and plain feedback.
- Keep saved moments, followed neighbors, joined groups, and meetup plans together in one personal circle.
- Keep walk timing, meeting points, pace, status, and related neighbor messages in one visible workflow.
- Let people share public moments, communities, meetups, and profiles through one predictable hub or a normal direct message.
- Require an explicit, reversible request before voice calling and keep messaging available throughout.
- Prefer small composable primitives over page-specific markup.
- Organize interface construction by capability (`ui`, `layout`, `object`, `feature`) so visual building blocks can be reused outside one branded screen.
- Keep newly created communities, meetups, and pet profiles openable, actionable, and shareable from every place where their cards appear.
- Keep public post replies useful and move sensitive details into direct messages.
- Treat an owner and each pet as separate social identities with independent URLs, follows, privacy, and profile content.
- Let one owner manage multiple pet profiles without merging their audiences or presenting animals as autonomous account operators.

## Owner And Pet Profiles

An owner profile represents the person responsible for the account. A pet
profile represents one animal and is always managed by people. Each identity has
its own canonical URL, follow target, profile statistics, visibility settings,
and content context.

Profile visibility is understandable and previewable. Owners can inspect what a
public visitor, follower, friend, or manager sees without exposing exact
addresses, private contact details, medical records, GPS history, microchip
data, or verification documents.

Scout, Nori, and future pets remain separate even when they share an owner.
Future publication work must record whether a moment was published by an owner
or on behalf of a specific pet.

## Publication Feed

The feed keeps separate publishing identities visible while combining useful
updates from owners, pets, friends, groups, verified specialists, shelters, and
local alerts. People can choose recommended or chronological order and switch
between following, friends, pets, local, groups, experts, shelters, alerts,
video, photos, saved posts, drafts, and archive views.

The first functional prototype supports text, photos, native video, questions,
lost-pet notices, adoption posts, curated polls, and repost presentation.
Managed publications can be drafted, edited, archived, restored, and deleted
through an explicit confirmation screen. Social actions include reactions,
comments, one-level replies, reposts, bookmarks, update subscriptions,
not-interested, mute, block, and private reports.

Mutable feed state remains browser-session-backed in the prototype. Database
storage, uploads, queues, moderation operations, ranking models, notification
delivery, and analytics are production milestones rather than simulated claims.

Posts use public places or approximate areas instead of home coordinates.
Sensitive media requires an explicit reveal, videos never autoplay, and
professional advice never implies a guaranteed medical result.

## Subscriptions And Profile Recommendations

Subscriptions are exact, one-way relationships. Following an owner never
silently follows their pets, and following one pet never introduces unrelated
owner or sibling-pet publications. Friendship remains a separate mutual
relationship with separate privacy meaning.

The connection center at `/circle/connections` separates following, followers,
requests, and recommendations. Each subscription can be favorited, muted, or
given an independent notification level. Private profiles receive reversible
follow requests instead of immediate access.

Recommendations mix nearby people, pets, verified specialists, shelters,
groups, and topics. Every recommendation includes a plain-language reason.
People can follow, request access, dismiss, undo, or block without an automatic
subscription to adjacent profiles.

The Following feed uses exact active targets and excludes muted or blocked
relationships. Production social-graph storage, counters, cache invalidation,
notification delivery, anti-bot systems, and personalized ranking remain
separate milestones rather than simulated capabilities.

## Accessibility & Inclusion

Target WCAG 2.1 AA. Support keyboard navigation, visible focus, reduced motion, 44-by-44-pixel mobile touch targets, 200% text zoom, meaningful alternative text, labeled fields, and states that remain understandable without color.
