# Implementation Plan: PawCircle Events MVP

## Requirement Ledger

Status values are `pending`, `in_progress`, `implemented`, `verified`,
`blocked`, `later`, and `superseded`.

| Ledger ID | Source points | Requirement surface | Acceptance evidence | Status |
| --- | --- | --- | --- | --- |
| EVT-001 | 1 | Complete event lifecycle | Discovery through post-event state is navigable | verified |
| EVT-002 | 2-13 | Walk and outdoor event types | Catalog, labels, safety, place and route summaries | implemented |
| EVT-003 | 14-24 | Training, show, lecture, webinar, expert live types | Type-specific metadata and detail content | implemented |
| EVT-004 | 25-38 | Adoption, shelter, volunteer, search, charity, creative, travel, private and online types | Catalog coverage and contextual rules | implemented |
| EVT-005 | 39-55 | Event creation | Validated composer captures organizer, schedule, format and metadata | verified |
| EVT-006 | 56-61 | Event privacy and applications | Public, closed, hidden/friends/group concepts and application gate | implemented |
| EVT-007 | 62-71 | Dates, timezones, schedules and series semantics | Timezone-safe display and explicit later boundary for recurring series | implemented |
| EVT-008 | 72-80 | Place, route, online link and accessibility | Generalized public location and protected exact details | verified |
| EVT-009 | 81-94 | Owner, guest and pet eligibility | One registration links selected pets and private requirements | verified |
| EVT-010 | 95-112 | Registration statuses, approval and waitlist | Deterministic transitions and duplicate prevention | verified |
| EVT-011 | 113-128 | Free/paid tickets, payment and refund states | Safe no-card prototype payment flow and receipt | verified |
| EVT-012 | 129-138 | Event page structure | Header, status, tabs, overview, organizers, people, pets, map, files and FAQ | verified |
| EVT-013 | 139-146 | Chat, announcements, questions, polls and change history | Server-validated messages and visible updates | verified |
| EVT-014 | 147-152 | Notifications and reminders | Configurable reminder/calendar state and urgent-change copy | verified |
| EVT-015 | 153-158 | Personal, pet and group calendar semantics | Add/remove state and timezone/conflict information | implemented |
| EVT-016 | 159-163 | Weather | Manual weather advisory representation; automatic provider is later | implemented |
| EVT-017 | 164-186 | Rules, emergency and safety | Rules consent, safety plan, clinic and private reporting | verified |
| EVT-018 | 187-194 | QR/manual check-in and attendance | Unique ticket reference and idempotent attendance state | verified |
| EVT-019 | 195-201 | Photo/video consent, album, stream and recap | Consent-aware gallery and explicit streaming boundary | implemented |
| EVT-020 | 202-209 | Feedback, reviews, badges and certificates | Verified-attendance review flow; certificates are later | verified |
| EVT-021 | 210-220 | Search, map, calendar and recommendations | Filters, modes, reasons, hiding and organizer context | verified |
| EVT-022 | 221-229 | Moderation and appeals | Report action, verification labels and moderator-state copy | implemented |
| EVT-023 | 230-235 | Spam, duplicate, ticket fraud, unsafe links and sponsorship | Rate-limited actions and transparent commercial labels | implemented |
| EVT-024 | 236-242 | Completion, repeat, series and archive | Completed state and safe copy workflow; full series is later | implemented |
| EVT-025 | 243-251 | Registration, attendance, financial, notification and safety analytics | Aggregate organizer dashboard with privacy limits | verified |
| EVT-026 | 252-255 | Accessibility, captions, quiet zone and pet-free attendance | Accessible controls/content and explicit later media services | verified |
| EVT-027 | 256-260 | Mobile experience and poor connection | Compact actions, stable dimensions and cached-detail explanation | verified |
| EVT-028 | 261-262 | Desktop event and organizer workspaces | Two-column detail and operational organizer panel | verified |
| EVT-029 | 263-265 | Multilingual content | Language metadata and explicit later translation boundary | implemented |
| EVT-030 | 266-276 | Technical invariants | One registration, capacity lock, payment/check-in idempotency and privacy cache semantics | verified |
| EVT-031 | 277 | Required MVP | Every listed MVP capability is implemented and verified | verified |
| EVT-032 | 278 | Later capabilities | Visible backlog; no false completion claim | later |
| EVT-033 | 279 | Group walk scenario | End-to-end free application, approval, reminder, QR and gallery flow | verified |
| EVT-034 | 280 | Paid training scenario | Paid registration, ticket, payment and session status flow | verified |
| EVT-035 | 281 | Show scenario | Category, document guidance, payment and QR flow represented | implemented |
| EVT-036 | 282 | Weather reschedule scenario | Change history and attendee re-confirmation | verified |
| EVT-037 | 283 | Incident scenario | Safety guidance and private report without public pet labeling | verified |
| EVT-038 | 284 | Search action scenario | Urgent event, volunteer state and completion update represented | implemented |
| EVT-039 | 285 | Private celebration scenario | Protected location, invitation context and private album rules | implemented |
| EVT-040 | 286 | Paid webinar scenario | Timezone, payment, protected link and post-event materials | implemented |

## Exact Source Crosswalk

Every numbered source point is assigned exactly once:

- `EVT-001`: 1
- `EVT-002`: 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13
- `EVT-003`: 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24
- `EVT-004`: 25, 26, 27, 28, 29, 30, 31, 32, 33, 34, 35, 36, 37, 38
- `EVT-005`: 39, 40, 41, 42, 43, 44, 45, 46, 47, 48, 49, 50, 51, 52, 53, 54, 55
- `EVT-006`: 56, 57, 58, 59, 60, 61
- `EVT-007`: 62, 63, 64, 65, 66, 67, 68, 69, 70, 71
- `EVT-008`: 72, 73, 74, 75, 76, 77, 78, 79, 80
- `EVT-009`: 81, 82, 83, 84, 85, 86, 87, 88, 89, 90, 91, 92, 93, 94
- `EVT-010`: 95, 96, 97, 98, 99, 100, 101, 102, 103, 104, 105, 106, 107, 108, 109, 110, 111, 112
- `EVT-011`: 113, 114, 115, 116, 117, 118, 119, 120, 121, 122, 123, 124, 125, 126, 127, 128
- `EVT-012`: 129, 130, 131, 132, 133, 134, 135, 136, 137, 138
- `EVT-013`: 139, 140, 141, 142, 143, 144, 145, 146
- `EVT-014`: 147, 148, 149, 150, 151, 152
- `EVT-015`: 153, 154, 155, 156, 157, 158
- `EVT-016`: 159, 160, 161, 162, 163
- `EVT-017`: 164, 165, 166, 167, 168, 169, 170, 171, 172, 173, 174, 175, 176, 177, 178, 179, 180, 181, 182, 183, 184, 185, 186
- `EVT-018`: 187, 188, 189, 190, 191, 192, 193, 194
- `EVT-019`: 195, 196, 197, 198, 199, 200, 201
- `EVT-020`: 202, 203, 204, 205, 206, 207, 208, 209
- `EVT-021`: 210, 211, 212, 213, 214, 215, 216, 217, 218, 219, 220
- `EVT-022`: 221, 222, 223, 224, 225, 226, 227, 228, 229
- `EVT-023`: 230, 231, 232, 233, 234, 235
- `EVT-024`: 236, 237, 238, 239, 240, 241, 242
- `EVT-025`: 243, 244, 245, 246, 247, 248, 249, 250, 251
- `EVT-026`: 252, 253, 254, 255
- `EVT-027`: 256, 257, 258, 259, 260
- `EVT-028`: 261, 262
- `EVT-029`: 263, 264, 265
- `EVT-030`: 266, 267, 268, 269, 270, 271, 272, 273, 274, 275, 276
- `EVT-031`: 277
- `EVT-032`: 278
- `EVT-033`: 279
- `EVT-034`: 280
- `EVT-035`: 281
- `EVT-036`: 282
- `EVT-037`: 283
- `EVT-038`: 284
- `EVT-039`: 285
- `EVT-040`: 286

## Phase 1: Domain Foundation

- [x] Add `BrowseEventsRequest`.
- [x] Add stable event and content catalogs.
- [x] Add event state with registration, payment, waitlist, calendar, check-in,
  message, announcement, review, report, and change-history transitions.
- [x] Add event presenter for directory and detail view models.

## Phase 2: Discovery

- [x] Upgrade the event directory controller.
- [x] Add search, filters, sorting, and display modes.
- [x] Add explainable event recommendations.
- [x] Upgrade event cards with format, price, capacity, privacy, organizer, and
  status context.

## Phase 3: Event Detail

- [x] Add generic routes for built-in events.
- [x] Build event hero and tab navigation.
- [x] Build overview, schedule, people, pets, chat, announcements, location,
  media, rules, reviews, and organizer views.
- [x] Enforce exact-location and online-link disclosure boundaries.

## Phase 4: Creation

- [x] Expand the event composer fields.
- [x] Validate category, organizer, date, time, timezone, format, privacy,
  capacity, registration policy, ticket model, price, rules, and safety plan.
- [x] Keep created events visible through existing created-content routes.

## Phase 5: Registration And Tickets

- [x] Add interested, request, approve, decline, cancel, and waitlist actions.
- [x] Add free and paid ticket flows.
- [x] Add payment reservation, success, failure, retry, receipt, and cancellation
  states without collecting payment credentials.
- [x] Prevent duplicate registrations, seats, payments, and tickets.

## Phase 6: Operational Tools

- [x] Add chat and announcement actions.
- [x] Add reminder and calendar controls.
- [x] Add QR and manual check-in.
- [x] Add reschedule acknowledgement and event cancellation.
- [x] Add organizer registration and waitlist controls.

## Phase 7: Safety, Media, Feedback, Analytics

- [x] Add rules, safety, weather, accessibility, consent, and clinic information.
- [x] Add gallery and post-event recap content.
- [x] Add verified-attendance reviews.
- [x] Add event report flow.
- [x] Add aggregate organizer analytics and privacy caveats.

## Phase 8: UI And Verification

- [x] Build semantic event components in `ui`, `object`, and `feature`.
- [x] Add a focused event SCSS module without prefixed selectors.
- [x] Verify desktop, tablet, and mobile layouts.
- [x] Run lint, Pint, Blade cache, routes, build, prohibited-pattern scans, and
  browser checks.
- [x] Re-audit `EVT-001` through `EVT-040`.
- [x] Commit and push the complete PawCircle implementation through events
  without unrelated workspace artifacts.

## Known Boundary

This repository currently has no event Eloquent schema, authentication domain,
payment provider, queue-backed notifications, weather provider, map provider,
video provider, or GPS integration. The MVP is therefore a validated,
session-backed product prototype. Production persistence and external services
must replace those boundaries before launch.
