# Communication Architecture Decisions

Date: 2026-08-01

## ADR-COMM-001: Canonical Dialog And Participant Membership

Use one canonical dialog per participant set and business context. Store each
participant as a separate membership with the real `User`, represented
`SocialActor`, role, capabilities, join/leave time, notification preferences,
and read cursor. Personal, adoption, marketplace, support, and professional
contexts remain distinct even when the people are the same.

## ADR-COMM-002: Real Sender Is Immutable Audit Data

Every user message and critical mutation stores the authenticated account, the
represented profile, and a role snapshot. A pet or organization name may be
the primary presentation identity, but never becomes anonymity. System events
use a reserved type and cannot be forged through user text.

## ADR-COMM-003: Requests Precede Unknown Contact

Unknown contacts create a bounded request, not an unrestricted dialog.
Recipient settings, blocks, age policy, context, rate limits, account status,
and fraud signals are checked server-side before creation and again before
every send, attachment, invitation, location share, or call signal.

## ADR-COMM-004: Messages And Attachments Have Separate Lifecycles

A message owns ordering, sender attribution, type, reply relation, version,
status, expiry, moderation, and deletion state. Media and documents are
independent private objects with validated content type, scan state, retention,
access grants, and short-lived policy-checked delivery. Message deletion does
not silently destroy retained evidence or authoritative documents.

## ADR-COMM-005: Domain Cards Reference Authoritative Objects

Pet, event, place, product, service, task, adoption, search, appointment, and
payment cards reference the owning domain and render a current authorized
projection. User text cannot imitate an official payment, support, booking, or
case status.

## ADR-COMM-006: Delivery Is Idempotent And Server Ordered

Every client send has a unique client identifier. The server assigns a stable
dialog sequence and returns the existing result on replay. Device delivery,
read state, retries, edits, and deletion are separate records or transitions;
"sent", "delivered", "read", and "acknowledged" are never conflated.

## ADR-COMM-007: Blocks And Expiry Contract Every Surface

A block, participant removal, role revocation, dialog expiry, device revocation,
or attachment-grant expiry immediately affects sends, calls, invitations,
location, push, search, cached history, and media URLs. Exclusions are checked
at use time, not only when the dialog was opened.

## ADR-COMM-008: Cryptography Requires A Reviewed Protocol

TLS and encrypted storage are baseline controls. E2EE is introduced only with
a reviewed multi-device protocol, device identity, rekeying, backup and key
loss semantics, local search, report disclosure, and metadata documentation.
The application will not implement bespoke cryptography or claim that the
server can both never see content and analyze all content centrally.

## ADR-COMM-009: Calls Require Signaling And Relay Infrastructure

Audio/video calls use WebRTC only after an approved signaling, STUN/TURN relay,
abuse, consent, recording, and retention design exists. Camera and microphone
remain off until explicit preflight consent. Direct peer IP exposure is not the
default for unknown contacts.

## ADR-COMM-010: Prototype State Is Compatibility Data

Existing `messaging.state.v1` rows remain readable and private. They may
support bounded UI preference migration, but cannot create shared messages,
consent, delivery receipts, calls, reports, or cryptographic facts.
