# Implementation Plan: PawCircle Places Map MVP

> Historical delivery record. Production requirements, security rules, testing gates, and runtime versions are governed by `docs/index.md` and its canonical documents.

## Requirement Ledger

Status values are `pending`, `in_progress`, `implemented`, `verified`,
`blocked`, `later`, and `superseded`.

| Ledger ID | Source points | Requirement surface | Acceptance evidence | Status |
| --- | --- | --- | --- | --- |
| MAP-001 | 1 | Complete place navigator | Discovery, detail, action, social and safety lifecycle is navigable | verified |
| MAP-002 | 2-7 | Map, catalog, modes, location and privacy | Modes preserve filters; location is optional and generalized | verified |
| MAP-003 | 8-17 | Parks, rules, quiet/night use, crowding, seasons and hazards | Park records and filters expose each contextual dimension | verified |
| MAP-004 | 18-27 | Dog parks, zones, equipment, safety, rules, live state and rental | Dedicated dog-park metadata and temporary warning state | verified |
| MAP-005 | 28-35 | Walking routes, difficulty, shortening, hazards and sharing privacy | Route detail and home-point protection are visible | verified |
| MAP-006 | 36-39 | Beaches, water quality and winter safety | Water places carry source, freshness and non-guarantee copy | verified |
| MAP-007 | 40-56 | Veterinary clinics, species, services, emergency, booking boundary, verification and reviews | Emergency species-aware clinic flow and complete clinic detail | verified |
| MAP-008 | 57-58 | Veterinary pharmacies and medicine search safety | Category and explicit no-prescription/no-guarantee boundary | verified |
| MAP-009 | 59-63 | Pet stores, stock, reservation and ethical filters | Catalog representation and explicit live-inventory boundary | verified |
| MAP-010 | 64-71 | Grooming, pet filters, specialists, booking, needs, media and mobile service | Cat/quiet grooming discovery and protected notes | verified |
| MAP-011 | 72-75 | Trainers, training places, task search and verification | Service filters and exact verification labels | verified |
| MAP-012 | 76-80 | Boarding, daycare, home sitting and safety | Category previews with generalized private service zones | verified |
| MAP-013 | 81-85 | Pet-friendly cafes, levels, rules, quiet hours and menu safety | Cafe detail and transparent access restrictions | verified |
| MAP-014 | 86-88 | Pet-friendly hotels, fees and pet confirmation | Catalog preview with later booking boundary | verified |
| MAP-015 | 89-91 | Pet transport and sourced rules | Transport category preview and dated source requirement | verified |
| MAP-016 | 92-95 | Shelters, adoption centers and microchip services | Shelter detail, events, rules and service filters | verified |
| MAP-017 | 96-100 | Water, waste, washing, studios and memorial services | Utility categories and non-aggressive memorial discovery | verified |
| MAP-018 | 101-105 | Search, suggestions, natural language, current area and history | Parsed filters are shown and history is controllable | verified |
| MAP-019 | 106-118 | Category, distance, hours, pet, leash, access, safety, price, rating, verification, crowd and time filters | Every filter has a validated query option and result effect | verified |
| MAP-020 | 119-120 | Sorting and organic recommendation | Deterministic sort and separate sponsored labeling | verified |
| MAP-021 | 121-125 | Result card, detail tabs, contact actions and hours | Complete card and place page with special-hour status | verified |
| MAP-022 | 126-135 | Place creation, natural places, duplicates, claims, branches and employee roles | Validated creation, duplicate response and claim workflow | verified |
| MAP-023 | 136-143 | Verification, freshness, community checks, corrections and history | Source-specific labels and moderated correction state | verified |
| MAP-024 | 144-148 | Gallery, date, hazard media, privacy and video overview | Structured gallery with dated and privacy-aware media | verified |
| MAP-025 | 149-158 | Reviews, criteria, pet context, anonymity, editing, responses and fraud | Review workflow, owner response and report controls | verified |
| MAP-026 | 159-161 | Questions, responder identity and official answers | Place Q&A with visible author type | verified |
| MAP-027 | 162-166 | Check-in privacy, expiry, friend context and invitations | Private default check-in and social planning actions | verified |
| MAP-028 | 167-170 | Favorites, private/public and collaborative collections | Save and collection state with privacy labels | verified |
| MAP-029 | 171-175 | Routing, pet-aware stops, entrance and offline boundary | Route links, entrance detail and explicit offline backlog | verified |
| MAP-030 | 176-179 | Event map layer, place events and create-from-place | Events appear in place context and prefill creation | verified |
| MAP-031 | 180-183 | Lost/found layer and search coordination privacy | Layer representation without home or holding-address disclosure | verified |
| MAP-032 | 184-188 | Temporary warnings, statuses, expiry, subscriptions and abuse protection | Warning state can be submitted, confirmed, resolved and expired | verified |
| MAP-033 | 189-192 | Weather, heat, cold, rain and mud | Manual advisory presentation with provider boundary | verified |
| MAP-034 | 193-197 | Place feed, follow, friend context, recommendations and expiring stories | Follow state and structured social updates | verified |
| MAP-035 | 198-202 | Specialist schedule, quick booking, management, waitlist and live boundary | Booking preview and explicit unavailable integration state | verified |
| MAP-036 | 203-205 | Promotion, organic ranking and business premium tools | Sponsored labels never alter organic explanation | verified |
| MAP-037 | 206-211 | Place, hazard and business reports, moderator actions, appeals and audit | Private report action and visible moderation states | verified |
| MAP-038 | 212-215 | Names, descriptions, reviews, rule and hazard translation | Language metadata and non-distorted official names | verified |
| MAP-039 | 216-220 | Map alternatives, keyboard, screen readers, color and saved access filters | Full list equivalent and keyboard marker operation | verified |
| MAP-040 | 221-225 | Mobile map, bottom card, one-hand use, weak connection and battery | Responsive controls and on-demand location | verified |
| MAP-041 | 226-227 | Desktop split view and comparison | Filters, results, map, detail and comparison workspace | verified |
| MAP-042 | 228-231 | Owner, platform and public analytics with privacy | Aggregate-only analytics and explicit private exclusions | verified |
| MAP-043 | 232-243 | Unified model, categories, branches, polygons, conflicts, cache, geosearch, clusters, layers, history and exports | Catalog invariants and privacy-aware map output | verified |
| MAP-044 | 244 | Required MVP categories | All nine named categories have records and filters | verified |
| MAP-045 | 245 | Required MVP functions | Every named MVP function is implemented and verified | verified |
| MAP-046 | 246 | Later capabilities | Explicit backlog with no false connected-service claims | later |
| MAP-047 | 247 | Quiet evening park scenario | Filters compare three parks and support collection and route | verified |
| MAP-048 | 248 | Small-dog fenced park scenario | Warning changes selection and supports follow update | verified |
| MAP-049 | 249 | Emergency avian clinic scenario | Only open bird-capable clinics with call-first warning | verified |
| MAP-050 | 250 | Community place creation scenario | Duplicate check, evidence and community-added status | verified |
| MAP-051 | 251 | Cafe correction scenario | Evidence, owner confirmation and follower update | verified |
| MAP-052 | 252 | Travel collection scenario | Cross-category private collection and offline boundary | verified |
| MAP-053 | 253 | Broken-glass warning scenario | Photo-ready warning, confirmations, resolution and archive | verified |
| MAP-054 | 254 | Quiet cat grooming scenario | Species, quiet handling, availability and private note flow | verified |
| MAP-055 | 255 | Group place-to-event scenario | Place filters and prefilled event creation | verified |

## Exact Source Crosswalk

Every numbered source point is assigned exactly once:

- `MAP-001`: 1
- `MAP-002`: 2, 3, 4, 5, 6, 7
- `MAP-003`: 8, 9, 10, 11, 12, 13, 14, 15, 16, 17
- `MAP-004`: 18, 19, 20, 21, 22, 23, 24, 25, 26, 27
- `MAP-005`: 28, 29, 30, 31, 32, 33, 34, 35
- `MAP-006`: 36, 37, 38, 39
- `MAP-007`: 40, 41, 42, 43, 44, 45, 46, 47, 48, 49, 50, 51, 52, 53, 54, 55, 56
- `MAP-008`: 57, 58
- `MAP-009`: 59, 60, 61, 62, 63
- `MAP-010`: 64, 65, 66, 67, 68, 69, 70, 71
- `MAP-011`: 72, 73, 74, 75
- `MAP-012`: 76, 77, 78, 79, 80
- `MAP-013`: 81, 82, 83, 84, 85
- `MAP-014`: 86, 87, 88
- `MAP-015`: 89, 90, 91
- `MAP-016`: 92, 93, 94, 95
- `MAP-017`: 96, 97, 98, 99, 100
- `MAP-018`: 101, 102, 103, 104, 105
- `MAP-019`: 106, 107, 108, 109, 110, 111, 112, 113, 114, 115, 116, 117, 118
- `MAP-020`: 119, 120
- `MAP-021`: 121, 122, 123, 124, 125
- `MAP-022`: 126, 127, 128, 129, 130, 131, 132, 133, 134, 135
- `MAP-023`: 136, 137, 138, 139, 140, 141, 142, 143
- `MAP-024`: 144, 145, 146, 147, 148
- `MAP-025`: 149, 150, 151, 152, 153, 154, 155, 156, 157, 158
- `MAP-026`: 159, 160, 161
- `MAP-027`: 162, 163, 164, 165, 166
- `MAP-028`: 167, 168, 169, 170
- `MAP-029`: 171, 172, 173, 174, 175
- `MAP-030`: 176, 177, 178, 179
- `MAP-031`: 180, 181, 182, 183
- `MAP-032`: 184, 185, 186, 187, 188
- `MAP-033`: 189, 190, 191, 192
- `MAP-034`: 193, 194, 195, 196, 197
- `MAP-035`: 198, 199, 200, 201, 202
- `MAP-036`: 203, 204, 205
- `MAP-037`: 206, 207, 208, 209, 210, 211
- `MAP-038`: 212, 213, 214, 215
- `MAP-039`: 216, 217, 218, 219, 220
- `MAP-040`: 221, 222, 223, 224, 225
- `MAP-041`: 226, 227
- `MAP-042`: 228, 229, 230, 231
- `MAP-043`: 232, 233, 234, 235, 236, 237, 238, 239, 240, 241, 242, 243
- `MAP-044`: 244
- `MAP-045`: 245
- `MAP-046`: 246
- `MAP-047`: 247
- `MAP-048`: 248
- `MAP-049`: 249
- `MAP-050`: 250
- `MAP-051`: 251
- `MAP-052`: 252
- `MAP-053`: 253
- `MAP-054`: 254
- `MAP-055`: 255

## Phase 1: Domain Foundation

- [x] Add validated browse and composer inputs.
- [x] Add stable place and content catalogs.
- [x] Add place state for saves, follows, check-ins, collections, submissions,
  corrections, warnings, reviews, questions, claims, reports, and history.
- [x] Add place presenter for directory and detail view models.

## Phase 2: Discovery And Map

- [x] Add the place directory routes and controller.
- [x] Add search, natural-language parsing, filters, sorting, and view modes.
- [x] Add semantic markers, clusters, layers, synchronized selection, zoom,
  fullscreen, route, current-area and keyboard behavior.
- [x] Add complete list equivalent and recommendation explanations.

## Phase 3: Emergency Veterinary Mode

- [x] Prioritize open, 24-hour and on-call clinics.
- [x] Filter by accepted species.
- [x] Add call, route, freshness and call-first actions.
- [x] Remove unrelated commercial categories from emergency mode.

## Phase 4: Place Detail

- [x] Add place hero and tab navigation.
- [x] Add overview, gallery, services, rules, prices, schedule, specialists,
  reviews, events, questions, map, updates, and correction views.
- [x] Keep private location, check-in and moderation data protected.

## Phase 5: Social And Community Actions

- [x] Add favorite, collection, follow, visit and private check-in actions.
- [x] Add friend context, invitations and event creation from a place.
- [x] Add place creation with duplicate detection and community status.
- [x] Add owner claim and verification-scope labels.

## Phase 6: Corrections, Warnings And Moderation

- [x] Add correction evidence, statuses and history.
- [x] Add temporary warnings, confirmations, expiry, resolution and archive.
- [x] Add place, hazard, business, review and owner report flows.
- [x] Add review, question and official-answer interactions.

## Phase 7: Routes, Events And Privacy

- [x] Add route metadata, difficulty, hazards, shortcuts and entrance guidance.
- [x] Add route links and home-point privacy controls.
- [x] Add event layer and place-to-event creation context.
- [x] Add lost/found and live-service concepts as privacy-safe boundaries.

## Phase 8: UI And Verification

- [x] Build semantic place components in `ui`, `object`, and `feature`.
- [x] Add focused place SCSS without prefixed selectors.
- [x] Add minimal progressive-enhancement JavaScript without a framework.
- [x] Verify desktop, tablet, and mobile layouts.
- [x] Run lint, Pint, Blade cache, routes, build, prohibited-pattern scans, and
  browser checks.
- [x] Re-audit `MAP-001` through `MAP-055`.
- [x] Commit and push only the place-map implementation.

## Known Boundary

This repository currently has no place Eloquent schema, authenticated business
ownership domain, map provider, geocoder, routing provider, booking platform,
payment provider, weather feed, municipal-data importer, live inventory,
background GPS, or queue-backed notification system.

The MVP is therefore a validated, session-backed product prototype with
provider-ready boundaries. Production persistence and external adapters must
replace those boundaries before launch.
