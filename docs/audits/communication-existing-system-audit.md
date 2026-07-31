# Communication Existing System Audit

Date: 2026-08-01

## Presentation Prototype

The authenticated `/messages` routes render Blade preview screens backed by
`MessageCatalog`, `MessagePresenter`, `ConversationPresenter`, and
`MessageState`. The interface demonstrates requests, folders, message types,
tasks, reports, and call controls in EN/LT/RU, but its catalogue is fixed and
its mutations are private to the current user.

`MessageState` stores bounded arrays in encrypted `UserDomainState` namespace
`messaging.state.v1`. `PerformMessageAction` changes that one user's prototype
state. It does not deliver a message to another account, establish shared
participants, authorize a real represented profile, create a server delivery
receipt, scan a file, start WebRTC, or establish E2EE. Preview labels such as
"sent", "scan queue", or "connected" are interface simulations and are not
production evidence.

## Existing Durable Communication-Like Data

The repository already has specialized durable records such as
`ForumMentorshipMessage` and `ForumEventMessage`. Forum comments, adoption
workflows, marketplace records, reports, evidence, notifications, and audit
events also carry domain communication. These remain authoritative in their
own modules and must be linked to a canonical dialog rather than copied into a
second mutable truth.

## Reusable Foundations

- `User`, `SocialActor`, pet manager permissions, social relationships, account
  blocks, groups, experts, organizations, and active-account state;
- unified report, moderation case, evidence, appeal, and audit boundaries;
- private storage patterns, validated uploads, EN/LT/RU Blade components,
  policies, factories, cursor pagination, and encrypted user state;
- canonical content, domain links, and represented/real actor attribution from
  the content foundation.

## Missing Production Capabilities

There is no canonical dialog, participant membership, contact request,
message, delivery receipt, device session/key, attachment grant, call session,
group-chat role, retention policy, per-recipient deletion, communication
search index, or cross-account notification contract. There is also no
websocket/SSE delivery service, WebRTC signaling/TURN provider, antivirus
worker, external translation service, or approved E2EE protocol/runtime.

## Compatibility Boundary

The first communication migration must be additive. It preserves the
`messaging.state.v1` row and every specialized message record. A compatibility
report may count rows and map deterministic catalogue keys, but it must not
promote private prototype arrays into shared consent, delivery, participant,
call, block, or report facts.

## Main Risks

- claiming simulated local state as cross-user messaging;
- inventing cryptography instead of adopting a reviewed protocol;
- letting represented profiles hide the authenticated sender;
- allowing a direct URL, stale device, attachment link, call signal, or search
  index to bypass a block or expired membership;
- duplicating marketplace, adoption, lost/found, support, or professional
  status as forgeable chat text;
- enabling AI or server search over E2EE content without an honest consent and
  device-processing model.
