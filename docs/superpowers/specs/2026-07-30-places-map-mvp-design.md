# PawCircle Places Map MVP Design

> Product design source. Preserve its domain detail; current production, security, persistence, and verification requirements are governed by `docs/index.md`.

## Goal

Build a privacy-aware navigator for pet owners that combines a visual map,
searchable catalog, place details, emergency veterinary discovery, routes,
events, community corrections, temporary safety warnings, reviews, questions,
and social planning.

The current application is a session-backed Blade prototype. The MVP must
provide real validated interactions and state transitions while clearly
separating prototype data from external geocoding, live inventory, booking,
weather, payment, GPS, and official verification services.

## Product Boundary

The MVP implements the complete surface named in source points 244 and 245:

- parks, dog parks, veterinary and 24-hour clinics, pet stores, grooming,
  shelters, pet-friendly cafes, and walking routes;
- map, catalog, combined, fullscreen, route, favorites, visited, events, and
  warning modes with filters preserved across views;
- voluntary generalized location, manual city or area selection, and complete
  search without location permission;
- pet-aware search suggestions, natural-language query interpretation, category
  filters, distance, open-now, species, size, leash, accessibility, safety,
  price, rating, verification, crowd, time, and sorting controls;
- place cards and full place pages with photos, hours, contacts, rules,
  services, pricing, specialists, events, questions, reviews, updates,
  verification, and freshness information;
- favorites, personal collections, subscriptions, private check-ins, visit
  history, friend context, invitations, and event creation from a place;
- community place creation, duplicate detection, owner claim, correction
  evidence, review workflow, and change history;
- temporary warnings with expiry, confirmation, resolution, source and
  moderation states;
- emergency veterinary search that prioritizes open clinics accepting the
  selected species and provides call and route actions without a false
  availability guarantee;
- accessible keyboard navigation, a complete text alternative to the map,
  stable mobile controls, and a desktop split workspace.

Source point 246 remains an explicit post-MVP boundary:

- external appointment and payment processing;
- private dog-park rental;
- live store inventory and specialist marketplace records;
- live crowding, GPS route recording, and offline map packages;
- provider-backed weather, tick, and water-quality feeds;
- automatic pet-profile filters, live check-in presence, and collaborative
  collection editing;
- external platform and professional verification;
- production business analytics and transport integrations;
- sitter, hotel, memorial-service, and advanced accessibility workflows;
- automatic ingestion of official municipal and business data.

The directory can explain and preview these later concepts, but it must not
claim that unavailable providers are connected.

## Architecture

### Catalog

`PlaceCatalog` owns stable place identity, multiple categories,
branch-aware contact data, geographic coordinates, map positions, public
location accuracy, hours, species and size eligibility, pet rules,
accessibility, safety features, services, pricing, verification labels,
recommendation reasons, freshness, imagery, and integration capabilities.

One place can expose several categories and independently described services
without creating duplicate records.

### Content

`PlaceContentCatalog` owns galleries, operating schedules, rules,
service groups, route metadata, facilities, specialists, pricing, events,
seed reviews, questions, official answers, social context, accessibility
details, seasonal notes, and analytics summaries.

### State

`PlaceState` owns:

- saved places, visits, follows, collections, private check-ins, and recent
  views;
- generalized location consent without retaining a public home point;
- submitted places and duplicate-review state;
- corrections, evidence, statuses, and change history;
- temporary warnings, confirmations, expiry, resolution, and reports;
- reviews, questions, owner answers, reports, and claim requests.

State transitions use a dedicated session namespace. Duplicate-sensitive
creation and correction transitions use cache locks and idempotent identifiers.

### Presentation

`PlacePresenter` combines catalog, content, session state, events, and
the active owner. It interprets validated filters and natural-language query
phrases, applies deterministic filtering and sorting, creates textual map
equivalents, and controls whether exact or generalized details are disclosed.

Controllers pass complete view models to Blade. Blade does not query data or
decide business transitions.

### Actions

The shared action endpoint validates place keys and action-specific payloads,
then delegates transitions to place state. Creation, correction, warning,
review, question, claim, and report workflows use the existing composer
architecture so every write has a Form Request and one server-side action.

## Map Experience

The map is a progressive enhancement over a complete server-rendered list:

- semantic marker buttons identify category, open status, distance, and place;
- selection synchronizes marker, result card, and detail preview;
- layers can show places, routes, events, warnings, and emergency clinics;
- zoom, fullscreen, list, route, and current-area controls are keyboard
  reachable;
- a route overlay is a visualization only; the route command opens a real
  OpenStreetMap directions URL;
- map markers cluster visually on smaller scales while emergency clinics remain
  individually discoverable;
- every map result also exists in the accessible result list.

No background GPS tracking is enabled. Browser geolocation starts only after a
clear user action, and only a rounded generalized origin is submitted.

## Discovery And Search

Search covers place names, addresses, neighborhoods, categories, services,
species, specialists, facilities, events, and descriptive traits.

Natural-language queries are translated into visible filter chips. Examples:

- "quiet evening park for a large dog" selects parks, lighting, quiet, open
  evening hours, and large-dog suitability;
- "24 hour clinic for a bird" selects emergency veterinary care, open now, and
  bird acceptance;
- "cat groomer with a quiet room" selects grooming, cats, and quiet-zone
  support.

Search never silently changes the user's home city or profile preferences.

## Emergency Veterinary Mode

Emergency mode removes distracting categories and prioritizes:

- clinics open according to the latest stored schedule;
- 24-hour or on-call services;
- acceptance of the selected species;
- distance and approximate travel time;
- direct telephone and route actions;
- freshness and verification labels.

It always advises the owner to call first. It never promises admission, a
specific clinician, an exact wait, or guaranteed treatment.

## Place Detail

The detail page supports overview, photos, services, rules, prices, schedule,
specialists, reviews, events, questions, map, updates, and corrections.

The header keeps the place, category, open status, location, rating, freshness,
verification, and primary actions immediately visible. Tabs are responsive and
keyboard navigable. Sensitive notes, private check-ins, exact user origins, and
moderation evidence are not shown publicly.

## Community Data Quality

- New submissions are marked community-added until claimed or verified.
- Duplicate detection compares normalized name, address, coordinates, phone,
  website, and category.
- A correction records old and proposed values, evidence, source, status,
  reviewer, and history.
- A single community confirmation never silently rewrites critical data.
- Owner claims distinguish identity, business, address, contact, and license
  verification.
- A claimed owner can update the profile but cannot remove unfavorable reviews.
- Important hours, rules, and hazard data always show a freshness label.

## Safety And Privacy

- Home addresses, precise pet residence, continuous GPS, movement history, and
  recurring walk times are never published.
- Published routes hide or generalize home-adjacent start and finish points.
- Check-ins are private by default and automatically expire.
- Temporary warnings carry a time, source, status, expiry, and confirmation
  count.
- Unverified warnings do not permanently damage a place reputation.
- Medical reviews cannot expose a pet medical record.
- Child faces, bystanders, vehicle plates, documents, and personal data are
  excluded from gallery guidance.
- Emergency, warning, and lost-pet surfaces avoid commercial distraction.

## Responsive And Accessible UI

- Mobile starts with search, map, categories, filters, list toggle, location,
  and emergency veterinary access.
- Primary actions sit within comfortable thumb reach and use at least 44-pixel
  targets.
- Desktop uses filters, results, map, and selected-place details in one
  operational workspace.
- The map has a complete text alternative, marker labels, visible focus, and
  non-color category cues.
- Result cards, dialogs, tabs, filters, and actions remain usable with keyboard
  and screen readers.
- Reduced-motion and low-bandwidth states are respected.

## External Integration Boundary

The MVP uses stable catalog coordinates and links to OpenStreetMap for route
navigation. It does not add a new mapping dependency or collect external API
keys.

Production requires provider adapters for geocoding, tiles, routing, real-time
traffic, hours, inventory, booking, payment, weather, water quality, official
alerts, and background synchronization. Each provider must use configuration,
rate limits, failure states, source attribution, and privacy review.

## Verification

- PHP syntax and Pint on every touched PHP file.
- Route listing, Blade cache, and production Vite build.
- Runtime scans for raw SQL, queries in Blade, `.pc-`, `x-pet-social`,
  duplicate IDs, inline JavaScript handlers, dead controls, and accidental
  unescaped output.
- Browser flows for search, filter persistence, map selection, emergency
  veterinary mode, favorites, collections, follows, check-in expiry, place
  creation, duplicate handling, correction, warning confirmation and
  resolution, review, question, claim, report, and event creation.
- Desktop, tablet, and mobile checks for overflow, image loading, map pixel
  content, console errors, keyboard focus, and responsive layout.
- No new PHP test files, following the explicit project constraint.
