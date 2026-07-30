# PawCircle Events MVP Design

## Goal

Turn meetups into an end-to-end event workspace for walks, training, shows,
adoption days, volunteer actions, celebrations, and online sessions. The
current application is a session-backed Blade prototype, so the implementation
must provide real validated state transitions without pretending that payment,
streaming, weather, GPS, or notification infrastructure already exists.

## Product Boundary

The MVP implements the complete surface named in source point 277:

- event creation with title, description, cover preset, category, organizer,
  date, time, timezone, place or online format, privacy, capacity, rules, and
  registration policy;
- searchable event discovery with filters, sorting, display modes,
  recommendation reasons, and calendar-oriented summaries;
- event detail with overview, schedule, attendees, pets, chat, announcements,
  location, media, rules, reviews, and organizer analytics;
- owner and pet registration, approval, capacity control, waitlist promotion,
  cancellation, rescheduling acknowledgement, and duplicate prevention;
- free and paid tickets, payment reservation and success/failure states,
  cancellation terms, and receipts represented as a safe prototype flow;
- reminders, personal calendar state, QR and manual check-in, attendance state,
  photos, reviews, reports, and basic organizer analytics;
- privacy-aware exact-location disclosure, event rules, safety information,
  report handling, and action history.

The following source point 278 capabilities remain explicit later work:

- recurring series and multi-day schedules;
- multiple rooms and hybrid attendance;
- temporary live location and offline check-in;
- deposits and advanced ticket rules;
- generated certificates, captions, and translation;
- participant subgroups and advanced analytics;
- external professional verification and automatic weather alerts;
- navigable route planning, transport seats, GPS integration;
- full incident management and charitable financial reporting.

## Architecture

### Catalog

`EventCatalog` owns stable event identity, discovery metadata, format,
privacy, capacity, ticket configuration, organizer verification labels,
location disclosure rules, and recommendation reasons.

### Content

`EventContentCatalog` owns schedule items, organizer team, FAQ,
attendees, pets, announcements, chat seed messages, files, gallery items,
rules, safety notes, reviews, and analytics seed data.

### State

`EventState` owns one registration per event occurrence and account.
It also owns waitlist order, payment state, check-in state, reminder/calendar
state, chat messages, announcements, reviews, reports, event status changes,
and action history. Capacity mutations use a session lock so the prototype does
not create duplicate seats during concurrent requests.

### Presentation

`EventPresenter` combines catalog, content, state, created-event
fixtures, and owner data. Controllers pass complete view models to Blade. Blade
performs no queries and does not decide business transitions.

### Actions

The shared action endpoint validates every event key and each event-specific
payload. Event transitions are delegated to event services, and redirects
preserve the active event tab or directory state.

## Discovery UX

The directory supports:

- text search over title, organizer, place, city, category, and tags;
- filters for recommended, walks, training, shows, online, free, and saved;
- soonest, closest, name, and recommended sorting;
- list, calendar, and map-summary modes without exposing private addresses;
- concise capacity, price, format, verification, and recommendation context;
- reversible interest state and direct navigation to event details.

## Detail UX

The event page uses a compact visual header with the event identity and primary
registration action. Horizontally scrollable tabs expose the full workflow
without stacking cards inside cards. Desktop uses a main workspace and a
restrained registration sidebar. Mobile uses a single column with stable media,
44-pixel controls, and direct access to ticket, map, chat, cancellation, delay,
and report actions.

Exact meeting points, online links, participant lists, and pet lists are
disclosed according to event privacy and registration status. A closed event
visitor sees only public identity and application requirements.

## Payment Boundary

No real payment details are collected or stored. The prototype:

- shows complete ticket pricing and cancellation terms;
- reserves a seat before payment;
- supports successful and failed payment outcomes;
- prevents duplicate prototype charges;
- emits a receipt reference after success;
- releases an expired or cancelled reservation;
- keeps refund and dispute states visible in the event model.

Production checkout requires a payment provider, webhook verification,
idempotency keys, money objects, ledger entries, tax rules, and reconciliation.

## Safety And Privacy

- Private and closed events never expose exact addresses in public cards,
  search, map summaries, or link previews.
- Online links are available only to eligible registered attendees.
- Medical, vaccination, temperament, child, and accessibility notes stay
  contextual and are never shown in public participant cards.
- Event compatibility language is advisory and never guarantees safety.
- Reports and incident-adjacent notes are private.
- Payment, emergency contact, and precise-location data are never included in
  analytics.
- Blocking does not reveal who blocked whom.

## Responsive And Accessible UI

- Controls have text labels, icons, visible focus, and non-color status cues.
- Touch targets are at least 44 pixels on compact viewports.
- Tables collapse into structured rows instead of horizontal overflow.
- Images use stable aspect ratios and alternative text.
- Map information has a textual equivalent.
- Registration status and errors are announced in text.
- Reduced-motion preferences are respected by the existing shell.

## Verification

- PHP syntax and Pint on every touched PHP file.
- Route listing, Blade cache, and production Vite build.
- Runtime scans for `.pc-`, `x-pet-social`, duplicate IDs, dead controls, and
  accidental raw HTML output.
- Browser flows for discovery, free registration, application approval,
  waitlist, paid ticket, payment failure and retry, reschedule acknowledgement,
  chat, announcement, calendar, QR check-in, cancellation, review, and report.
- Desktop, tablet, and mobile checks for overflow, control size, image loading,
  console errors, keyboard focus, and responsive layout.
- No new PHP test files, following the explicit project constraint.
