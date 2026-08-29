# Communication Master Plan

Date: 2026-08-01

Status: not started at the atomic evidence boundary; all 3,877 communication
IDs remain open.

## Contract

The safe-communication revision is preserved verbatim and contributes 3,877
stable `communication.*` requirements to the combined catalogue. Its source
payload SHA-256 is
`2ce8003da95e9846d797eba703614c5bf13fd61f740537703fe35858443b08dc`;
the combined catalogue SHA-256 is
`e2bbf22bf9b8dd42f7b7e1d1ee691391879cb80e39146abfd47f46932425d049`.
No requirement is verified from a preview label or design document alone.

## Phase 46: Dialog, Contact, And Identity Foundation

Create canonical dialogs, participant memberships, represented and real actor
attribution, typed contexts, statuses, capabilities, contact settings, bounded
message requests, acceptance restrictions, denial, expiry, duplicate control,
idempotency, immutable audit, policies, factories, and compatibility reporting.
Preserve prototype and specialized message records.

## Phase 47: Messages, Media, Files, And Structured Sharing

Add server-ordered text messages, replies, edits, per-user and permitted global
deletion, reactions, pins, bookmarks, forwarding limits, photos, video, voice,
files, safe link/QR previews, one-time and live location, and authoritative pet,
event, place, product, service, and task cards. Attachments require private
storage, content validation, scan states, metadata removal, retention, grants,
and protected delivery.

## Phase 48: Delivery, Devices, Workflow, And Archive

Implement durable outbox/inbox delivery, retries, receipts, offline queue,
multi-device sessions, read cursors, draft conflicts, presence privacy, typing
ephemera, scheduled messages, notifications, quiet hours, folders, archive,
search, export, retention, deletion, and compatibility-safe backups.

## Phase 49: Group Communication

Add group-chat ownership, administrators, moderators, members and guests;
invitations, expiring links/QR, approvals, role capabilities, read-only and slow
modes, raid protection, topics, polls, shared files, tasks, exit/removal,
ownership transfer, archive, and deletion safeguards.

## Phase 50: Audio And Video Calls

Select and integrate reviewed WebRTC signaling and TURN relay infrastructure.
Implement audio, video, and group calls, recipient permissions, preflight,
waiting room, host controls, missed-call context, weak-network degradation,
captions, text fallback, screen sharing, background privacy, and explicit
recording consent. No call capability is claimed before end-to-end browser and
relay verification.

## Phase 51: Encryption And Key Lifecycle

Verify transport and storage encryption, then introduce optional E2EE through
an approved protocol: device identity, safety verification, new/lost-device
handling, encrypted backup, key-loss disclosure, group rekeying, local search,
report disclosure, and metadata minimization. Service/support dialogs clearly
declare when they use server-readable encryption instead.

## Phase 52: Safety, Fraud, Moderation, And Optional AI

Integrate contact throttling, spam and forwarding controls, phishing and fake
support detection, credential-code warnings, marketplace/adoption/lost-found
fraud, harassment, stalking, threats, doxxing, sexual content, minor safety,
animal cruelty, illegal trade, account-wide block, restriction, safety mode,
structured reports, protected evidence, explanations, and appeals. AI remains
consent-bound, draft-only, compatible with E2EE, and unable to prescribe or
make final high-risk decisions.

## Phase 53: Interface, Localization, Accessibility, And Data Controls

Complete the responsive Blade/Livewire communication center, honest status and
encryption labels, notification privacy, EN/LT/RU originals and protected
translation fragments, screen-reader semantics, keyboard operation, large-text
reflow, non-colour status cues, captions, voice controls, and safe confirmation
for documents, location, calls, exports, and destructive actions.

## Phase 54: Quality, Release, And Scenario Verification

Verify delivery reliability, duplicates, latency, privacy contraction, safety
response, spam/fraud quality, all MVP type and privacy contracts, and every
selected ideal scenario. Run fresh migration, rollback, repeated compatibility
checks, full serial Pest, Pint, Larastan, Composer/NPM audits, production build,
cache compilation, desktop/mobile/320px browser checks, accessibility and
query inspection, complete traceability audit, staged diff review, commit, and
push.

## Global Invariants

- every user action has a real authenticated actor and optional represented
  profile;
- every dialog has a typed context, explicit participants, capabilities,
  status, expiry, retention, and audit;
- unknown contact begins as a constrained request;
- server authorization applies to every send, read, download, invite, call,
  location update, export, and search result;
- system events and authoritative domain cards cannot be forged by user text;
- a block or revoked role contracts all future access immediately;
- no permanent public URL exposes a private attachment;
- E2EE, AI, moderation, backup, search, and reporting claims remain mutually
  consistent and technically demonstrable;
- Blade performs no query, authorization, encryption, or business decision;
- unfinished requirements remain open.
