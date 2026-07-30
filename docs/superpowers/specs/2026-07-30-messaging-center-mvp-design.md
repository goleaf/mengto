# PawCircle Messaging Center — Design Specification

> Product design source. Preserve its domain detail; current production, security, persistence, and verification requirements are governed by `docs/index.md`.

## Contract

Point 8 defines one safe communication center for pet owners, co-owners, family members, groups, event participants, specialists, shelters, organizers, businesses, and platform support. A human account is always the accountable sender. Pet profiles, places, events, services, tasks, and documents are contextual cards and never autonomous senders.

The first stable release must work as a server-rendered Blade application with session-backed state. It must not claim that realtime WebRTC, media persistence, malware scanning, transcription, translation, end-to-end encryption, or external business queues work until providers are connected.

## Product Decisions

1. One inbox contains personal, family, group, event, professional, organization, search, support, and request contexts.
2. Unknown senders enter a request gate. Media, calls, payments, exact location, and bulk content stay blocked until acceptance.
3. Every conversation displays the responsible human or organization and only the public pet context explicitly linked to it.
4. Personal and professional conversations are visually distinct. Professional dialogs show case number, working hours, assigned staff, verification scope, and emergency limitations.
5. All mutations use a dedicated Form Request and Action. Blade remains query-free and contains no business decisions.
6. Session state is the prototype persistence boundary. It is intentionally separate from the older general preview state.
7. A call is an auditable session with consent, preflight, controls, quality state, and termination. Browser device preview is real; remote media transport is an explicit provider boundary.
8. Sensitive media and documents show metadata, access, scanning, consent, and expiry contracts before a real storage provider exists.
9. Blocking immediately affects sending and call controls. Reports retain selected context in a private moderation queue.
10. Desktop uses inbox, thread, and context rails. Mobile uses inbox first, then a dedicated thread view with a back control.

## Domain Shape

### Conversation

- Unique key and type.
- Human or verified organization identity.
- Linked pets and purpose.
- Privacy scope, role, membership, request status, and notification level.
- Per-user pinned, muted, archived, unread, restricted, and blocked flags.
- Optional channels, members, poll, tasks, professional case, and active call.

### Message

- Stable id, conversation, accountable sender, timestamp, type, text, reply reference, delivery status, and edit marker.
- Optional structured metadata for audio, photo, video, file, pet, place, event, task, announcement, professional answer, warning, or call.
- Per-user reaction, pin, bookmark, local deletion, and report context.
- Idempotent session-created key and bounded session history.

### Call

- Conversation, type, status, start/end timestamps, recording consent, device controls, quality state, and history.
- Device permission is requested only after a user action.
- Recording is off and cannot start silently.
- Realtime peer transport remains disconnected until an approved WebRTC provider is integrated.

## Security And Privacy

- No phone, email, home address, exact persistent GPS position, complete medical record, payment credentials, or external social account is disclosed by default.
- Photo GPS metadata is treated as removed-by-default contract; actual stripping belongs in the future upload pipeline.
- Medical files use explicit file-level and time-limited access contracts.
- Dangerous medical advice, threats, fraud, harassment, blackmail, animal cruelty, child-safety risk, and personal-data disclosure are report reasons.
- A report may preserve selected evidence despite sender-side deletion, under retention policy.
- Read receipts, presence, typing indicators, notification previews, and temporary location are user-configurable contracts.
- Export contains only data accessible to the requesting account.

## Provider Boundaries

| Capability | Prototype behavior | Required production provider |
| --- | --- | --- |
| Realtime delivery | Server POST and session state | WebSocket/realtime transport |
| Voice/video | Local preflight and call session state | WebRTC SFU/TURN/STUN |
| Recording | Explicitly off | Encrypted media storage and consent ledger |
| Uploads | Structured message card | Object storage, resumable upload, MIME and malware scan |
| Transcription | Transcript status and search contract | Speech-to-text provider with opt-out |
| Translation | Original/translation UX contract | Translation provider and protected token glossary |
| E2EE | Design boundary only | Audited protocol, device keys, recovery and report flow |
| Business queues | Case and assignment UI | Organization tenancy, staff authorization, SLA queue |
| Payments | Structured safety contract | PCI-compliant payment provider |

## Requirement Coverage Ledger

Every source requirement is assigned exactly once. The range validator expands these rows and must produce `296 assigned / 296 unique / no missing / no duplicates`.

| Requirements | Coverage | Delivery |
| --- | --- | --- |
| P8-001 | Safe communication center and complete communication lifecycle | Inbox, thread, context, calls, safety, search, export boundary |
| P8-002..P8-005 | Human accountability, pet context, multiple pets, personal/professional distinction | Catalog identity model, thread context, professional banner |
| P8-006..P8-020 | Personal, pet-friend, co-owner, family, group, event, vet, trainer, groomer, shelter, adoption, lost-pet, support dialogs | Eight representative dialog types plus extensible type/category fields |
| P8-021..P8-035 | Who may write, specialist exceptions, request gate, sender visibility, first-message limits, reasons, rate limits, minors, messaging off | Request status, request UI, catalog contracts, moderation/provider boundary |
| P8-036..P8-045 | Inbox folders, cards, pins, personal folders, archive, unread, reminders, protected dialogs, drafts, search | Inbox filters, state flags, local drafts, search; folders/reminders/protected vault are documented extension contracts |
| P8-046..P8-060 | Text, limits, Enter preference, preview, delivery states, retry, reply, quote, edit, deletion, scheduling, silent send | Composer, Ctrl+Enter, statuses, reply UI, session edit/delete, scheduling field, quiet-send state |
| P8-061..P8-070 | Reactions, pins, bookmarks, forwarding, links, mentions, all-mentions | Reaction/pin/bookmark actions; forwarding/mention permissions represented in group safety contract |
| P8-071..P8-080 | Pet/place/event/service/booking/task/poll/shopping/event-from-chat cards | Structured message renderer, tasks, poll, place/event/pet composer tools; service and booking remain schema-compatible cards |
| P8-081..P8-089 | Photos, quality, metadata, redaction, alt text, albums, one-time/expiring media, photo reports | Photo type, metadata/consent notices, report action; transformation/storage/expiry are upload-provider boundaries |
| P8-090..P8-095 | Video upload, captions, trimming, sensitive warnings, specialist video | Video card and captions/sensitivity contract; resumable encoding provider boundary |
| P8-096..P8-101 | Safe files, scanning, preview, temporary medical access, revocation, versions | File card and professional access metadata; scan/storage/version provider boundary |
| P8-102..P8-111 | Audio capture, duration, waveform, speed, resume, transcription, privacy, noise reduction, text fallback, reports | Audio message tool, waveform control, transcript metadata, report; capture/STT/DSP provider boundary |
| P8-112..P8-115 | Short video messages, preview, camera switch, captions and descriptions | Video composer and accessibility contract; camera capture/encoding provider boundary |
| P8-116..P8-128 | One-to-one audio, call requests, incoming/missed calls, controls, network state, group audio rooms and roles | Audio call preflight/session controls; remote transport and group rooms are post-MVP provider work |
| P8-129..P8-155 | One-to-one/group video, preflight, camera, layouts, screen sharing, captions, translation, recording consent, waiting room, consultation, notes, history, reports, quality | Local camera/mic preview, call state, audio fallback, consent and emergency UI; remote group/video/recording provider boundary |
| P8-156..P8-183 | Group creation, identity, roles, invitations, approval, departure, moderation, slow mode, channels, threads, polls, tasks, files, members, pseudonyms, logs, large-chat scaling | Group/event/search catalogs, channels, members, poll, tasks, role labels; administration and scale contracts documented |
| P8-184..P8-189 | Event chat admission, payment gate, exact location, attendance status, emergency notices, archive | Event dialog, attendee privacy, announcement/status cards, automatic-expiry contract |
| P8-190..P8-193 | Family care log, medication confirmation, duplicate warnings, quiet summaries | Family dialog, medication task, duplicate-warning message, care digest |
| P8-194..P8-202 | Working hours, greetings, after-hours response, staff assignment, internal notes, case status, templates, consented business broadcasts, opt-out | Professional banner/context and non-emergency warning; organization queue/provider boundary |
| P8-203..P8-208 | Consultation creation, intake, urgency warning, payment, follow-up, professional boundaries | Vet case, document, video-call and urgency surfaces; booking/payment provider boundary |
| P8-209..P8-212 | Text/audio/call translation and protected names/identifiers | Original-first translation contract and provider boundary |
| P8-213..P8-225 | Presence, typing, receipts, notification preview, disappearing messages, local deletion, export, account deletion, E2EE, transport/storage encryption, backups | Presence and delivery UI, local deletion/export actions, explicit E2EE/encryption/backup boundary |
| P8-226..P8-229 | New-device alerts, device list, step-up verification, compromised-account response | Security design contract; identity/session-management integration boundary |
| P8-230..P8-234 | Full owner/profile blocking, restriction, mute, call blocking | Working conversation block/restrict/mute actions and immediate send/call denial |
| P8-235..P8-250 | Message/dialog reports, evidence selection/retention, high-risk priority, cruelty/medical/fraud/social-engineering/stalking/child safety, moderation outcomes, explanations, appeals | Structured report action and reasons; priority routing, identity linking and appeals remain moderation-provider contracts |
| P8-251..P8-256 | Notification types, per-dialog settings, quiet hours, urgent exceptions, grouping, summaries | Notification-level state and context; push/email/SMS/digest provider boundary |
| P8-257..P8-261 | Message and transcript search, filters, contextual jumps, media gallery | Working message search and context rail; typed gallery categories |
| P8-262..P8-267 | Offline drafts, ordered queue, resumable upload, data saver, weak-call adaptation, reconnection | Local drafts and audio-only/reconnect controls; service worker/upload queue provider boundary |
| P8-268..P8-273 | Keyboard, screen readers, non-color states, captions, non-voice participation, reduced motion | Semantic log/dialog/forms, text labels, captions controls, keyboard focus, reduced-motion CSS |
| P8-274..P8-286 | Unique dialog, participant/message/call records, idempotency, ordering, sync/conflicts, pagination, rights/cache, temporary location, minimization | Stable catalog ids and session objects; production persistence/realtime/cache contracts |
| P8-287 | First stable release feature list | Delivered as working session-backed Blade MVP with call/provider caveat |
| P8-288 | Post-stabilization feature list | Explicit provider and roadmap boundaries; not falsely presented as connected |
| P8-289..P8-296 | Eight ideal scenarios | Represented by request, event, family, vet video, weak network, fraud safety, stalking controls, and lost-pet search dialogs |

## Acceptance Gates

| Gate | Verification | Pass |
| --- | --- | --- |
| Coverage | Expand every `P8-NNN` range | 296 assigned, 296 unique, no gaps or duplicates |
| Architecture | Static inspection | Controllers thin, dedicated requests/actions, no queries in Blade |
| Rendering | Blade cache and HTTP | Inbox, request, professional, group, family, search, call views return 200 |
| Actions | HTTP/session interaction | Request, message, reaction, flags, task, poll, block, report, call mutate visibly |
| Frontend | Vite build | JS and SCSS compile |
| Responsive | Playwright | Desktop, tablet, mobile without overlap or inaccessible controls |
| Accessibility | DOM inspection | Labeled forms, role log/dialog, focus styles, text statuses, reduced motion |
| Publication | Temporary Git index | Only point-8 files committed and pushed |
