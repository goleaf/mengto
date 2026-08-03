# Guest Join Landing Page Delivery Plan

Plan date: 2026-08-03

Status: implemented and verified on 2026-08-03

## Goal

Make `/` the clear public entry point for joining PawCircle while preserving a
direct application destination for existing members.

The page must answer three questions quickly:

1. What is PawCircle?
2. Why should a pet owner join?
3. What happens after account creation?

The plan uses the dated findings in
`docs/audits/pet-social-network-benchmark.md` and the current product,
security, privacy, localization, accessibility, and interface contracts.

## Previous State

- `routes/web.php` maps the named `home` route at `/` to
  `PreviewController`.
- `PreviewController` renders `resources/views/home.blade.php` with a complete
  preview feed.
- The guest response uses the member application shell, navigation, messages,
  notifications, and a fictional Mia Carter owner profile.
- The root response therefore looks like a signed-in prototype rather than a
  trustworthy invitation to create a real account.
- `/content` already owns the canonical persisted content feed.
- Guest login, registration, password recovery, email verification, active
  account enforcement, locale selection, and timezone settings already exist.

## Delivery Result

- `/` retains the stable `home` name and now uses `HomeController` plus a
  typed `HomeDestinationResolver` for guest, verified, unverified, and
  unavailable-account outcomes.
- Guests receive the passive `join.blade.php` noticeboard experience with a
  dedicated layout, canonical/Open Graph metadata, one account-creation goal,
  ordinary login navigation, and no application database query.
- Complete EN/LT/RU catalogues and a validated guest session-language action
  make the page selectable and usable before account creation.
- The old prototype feed still has active presentation and interaction test
  consumers, so it was not deleted. It moved to the authenticated,
  active-account-only `preview.feed` route and no longer leaks fictional
  member chrome into the guest root response.
- Browser automation now covers the join page at 320, 375, 768, 1024, 1440,
  and 1920 pixels across EN/LT/RU, including one `h1`, skip-link focus,
  44-pixel controls, overflow, external images, member-shell leakage, and
  console errors.

## Product Decision

Keep the stable `home` route name and change its audience behavior:

| Visitor state | `/` result | Primary action |
| --- | --- | --- |
| Guest | Render the localized join page | Create a free account at `register` |
| Active, unverified member | Redirect to `verification.notice` | Verify email |
| Active, verified member | Redirect to `content.index` | Open the community feed |
| Inactive authenticated account | Apply the existing inactive-session termination behavior | Return to account access |

This makes `/` a joining page for its intended public audience without making
existing members pass through acquisition copy on every visit.

## Positioning And Message Hierarchy

### Audience

Pet owners and animal caregivers who want calm, useful local connection and a
privacy-aware place for their animals, community activity, care context, and
safety workflows.

### Single promise

Create your pet's place in a calm, privacy-aware local community.

This sentence is a product direction, not final translated copy. Final copy
must be written as complete EN/LT/RU strings and checked against implemented
capabilities.

### Primary action

`Create your free profile` -> `route('register')`

The same action may recur after major sections, but no other action receives
equal visual weight. `Sign in` remains a clear text/secondary action for
returning members. Public exploration links may appear below the lead section
as ordinary navigation.

### Truthful proof

Initial proof comes from implemented behavior, not invented marketing data:

- one person can manage separate pet identities;
- pet visibility and discoverability are controllable;
- public location can remain approximate or hidden;
- block/report and scoped social-request controls exist;
- EN/LT/RU and explicit timezone handling exist;
- sensitive medical, care, device, and exact-location domains remain private.

No member count, testimonial, reunion count, professional guarantee, review
score, or safety promise may appear without current first-party evidence and a
documented owner for keeping it accurate.

## Page Structure

The visual concept is the existing **Neighborhood Noticeboard**, not a generic
oversized marketing hero. The page stays compact, practical, and centered on
real product outcomes.

1. **Guest header**
   - brand link;
   - quiet public exploration links where useful;
   - `Sign in`;
   - one primary `Create your free profile` action;
   - no private member navigation, notification icons, or fictional profile.
2. **Join introduction**
   - one `h1` with the single promise;
   - one short explanation;
   - primary join action and secondary sign-in link;
   - a local, first-party product composition rather than a stock-photo hero.
3. **Three practical outcomes**
   - give each pet a controlled profile;
   - find useful people, groups, places, events, and conversations;
   - keep safety and care context within clear privacy boundaries.
4. **How joining works**
   - create the account;
   - verify email;
   - add or skip a pet profile, review privacy, then enter the community.
5. **Product preview**
   - compact server-rendered examples of working feed, pet-profile, group, and
     lost/found surfaces;
   - labels distinguish available behavior from future roadmap items;
   - no external screenshots or competitor assets.
6. **What the member controls**
   - profile visibility, discoverability, approximate location, requests,
     recommendations, messaging, blocks, reports, language, and timezone;
   - link to the applicable privacy/safety documents or pages.
7. **FAQ**
   - cost and account creation;
   - whether a pet profile is required;
   - supported animals;
   - who sees location and private information;
   - how blocking/reporting works;
   - how account verification works.
8. **Final join action and public footer**
   - repeat the same primary join action;
   - include sign-in, privacy, safety, terms, accessibility, and language
     access without a second conversion goal.

## Architecture And File Placement

### Routing and controller

- Replace the root `PreviewController` binding with a final invokable
  `App\Http\Controllers\HomeController` while retaining `name('home')`.
- Keep the route inside the existing `web` middleware group.
- Delegate visitor-state resolution to one cohesive service or value object so
  the controller remains an HTTP adapter and inactive-account semantics are
  not copied from `EnsureActiveUser`.
- Redirect verified members to the persisted `content.index` feed rather than
  rebuilding that query for `/`.
- Remove `PreviewController` and the old root feed template only after `rg`
  proves they have no remaining route, test, or component consumer.

### Presentation

- Add a passive `resources/views/join.blade.php` page.
- Add a dedicated `resources/views/components/join-layout.blade.php`; the
  existing `app-shell` requires member navigation and an owner presentation,
  so it is the wrong contract for guests.
- Reuse existing brand, action, focus, typography, spacing, and color
  primitives where their prop contracts fit.
- Extract only genuinely repeated join sections into anonymous Blade
  components under `resources/views/components/join/`.
- Add a deliberate semantic landing layer to the existing SCSS entry point;
  do not create a second design system, introduce a framework, or use dynamic
  Tailwind class construction.
- Remove Unsplash preconnects from the join document and use no third-party
  runtime image dependency.

### Localization and metadata

- Add one `join.php` catalogue under `lang/en`, `lang/lt`, and `lang/ru`.
- Keep keys and placeholders identical and write complete sentences rather
  than fragments.
- Add prepared document title, meta description, canonical URL, Open Graph
  basics, and indexable semantic headings to the guest layout.
- Add FAQ structured data only if generated from the same prepared localized
  FAQ values rendered on the page; it must never create a second copy source.

### Data and queries

- Version one is static localized presentation and must issue no application
  database query for a guest.
- Do not query member counts, publications, pets, places, or testimonials for
  decoration.
- If verified aggregate proof is added later, it requires a model-owned,
  cached, invalidated aggregate with a measured query budget and truthful
  empty/failure behavior.
- The authenticated route only redirects; the query cost remains owned by the
  destination feed.

### Registration and onboarding

- Do not enlarge the existing registration form. It remains name, email,
  password, and confirmation.
- Preserve the current redirect to email verification.
- After verification, offer a skippable pet-profile setup path with a privacy
  explanation before public discoverability.
- Do not force location permission, external indexing, a public profile, or
  marketing consent during registration.

## Settings Integration

The landing page exposes only verified controls. It does not attempt to build
the complete preference center in the same work package.

### Already available to explain

- account locale and timezone;
- pet profile visibility, discoverability, external indexing, public location
  precision, and section rules;
- friend-request and follow policy;
- friend/follower list visibility;
- recommendation and message-request opt-in;
- block and report operations;
- selected connection, group, conversation, and lost/found alert controls.

### Follow-up settings package

The benchmark identifies a separate settings consolidation package, which
must be planned against exact open requirement IDs before implementation:

1. one authoritative notification center with channels, categories, quiet
   hours, digest frequency, emergency exceptions, and lost-alert radius;
2. account privacy review combining pet, social, message, location, indexing,
   recommendation, and block state without copying their authority;
3. measurement preference and locale-aware display;
4. active-session/security review;
5. marketing consent and personalization opt-out;
6. data export, retention explanation, account closure, and deletion status.

## Delivery Packages

### JP-01: Truthful capability and route contract

- inventory every prospective landing claim against current routes,
  implementation, policy, and test evidence;
- write guest/authenticated/unverified/inactive route tests first;
- preserve the `home` route name and registration/verification redirects;
- record any unsupported claim as excluded copy, not implied future behavior.

### JP-02: Guest presentation foundation

- implement the home destination resolver and thin controller;
- add the guest layout, header, join page, and passive reusable components;
- remove fictional member identity and private application controls from the
  guest document;
- retain ordinary non-JavaScript navigation for account access.

### JP-03: Localized noticeboard design

- write reviewed EN/LT/RU copy;
- implement the compact responsive composition with current design tokens;
- use first-party product visuals or HTML/CSS compositions only;
- add focus, reduced-motion, forced-colors, 200% zoom, and 44-pixel controls.

### JP-04: Metadata and onboarding continuity

- add truthful metadata and canonical URL;
- verify registration, verification, pet setup, privacy review, and member feed
  continuity;
- ensure the join page never exposes exact location, private content, or
  fictional proof.

### JP-05: Verification and publication

- run targeted route, auth, localization, architecture, and rendering tests;
- run Pint, Larastan, the serial full Pest suite, Vite production build, Blade
  cache, and applicable source/generator checks;
- browser-check 320, 375, 768, 1024, 1440, and 1920 pixels in EN/LT/RU;
- verify keyboard-only flow, one `h1`, skip link, no horizontal overflow, no
  console errors, no broken assets, and no undersized actionable controls;
- update documentation and evidence only for requirements actually proven;
- inspect the attributable diff, commit, and push directly on `main`.

## Tests And Acceptance Criteria

Add focused coverage, expected primarily in
`tests/Feature/JoinLandingPageTest.php`:

- a guest receives `200`, one `h1`, the join CTA, sign-in link, semantic
  sections, and no fictional owner/private member controls;
- `home` remains the route name for `/`;
- a verified active member is redirected to `content.index`;
- an unverified active member is redirected to `verification.notice`;
- an inactive session is terminated through the established account rule;
- EN/LT/RU render without raw keys and maintain key/placeholder parity;
- all join links resolve to named routes;
- guest rendering performs zero application database queries;
- no external image/preconnect dependency is present;
- metadata and any structured data match the visible localized page;
- existing registration, verification, authentication, and content-feed tests
  remain green.

Browser acceptance:

- no horizontal overflow from 320 through 1920 pixels;
- no missing or distorted assets;
- logical keyboard order starts with skip/navigation and reaches the join
  action and sign-in link;
- focus is always visible;
- all actionable targets are at least 44 by 44 pixels where applicable;
- 200% zoom, reduced motion, forced colors, and long RU/LT text remain usable;
- the browser console and network log contain no unexplained errors.

## Query Delta

| Path | Before | Measured after |
| --- | --- | --- |
| Guest `/` | Built the prototype feed presentation and complete app shell | Zero application DB queries; localized static join presentation |
| Verified member `/` | Built the prototype feed presentation | One redirect to `content.index`; feed queries remain owned and budgeted there |
| Registration and verification | Existing Livewire/auth flow | No additional query or field introduced by the landing page |

The zero-query guest result is enforced by `JoinLandingPageTest`; browser and
full-suite evidence is recorded below.

## Verification Evidence

- focused join/auth/architecture slice: 60 tests, 26,885 assertions;
- full serial Pest: 2,037 tests, 73,073 assertions in 108.887 seconds;
- Pint and Larastan level 5: passed with zero errors;
- Composer strict validation/audit and npm audit: zero advisories;
- production Vite build and config/event/route/view cache compilation: passed;
- isolated fresh SQLite: 111 migrations, 191 tables, and repeat seed preserved
  five users;
- immutable forum source and all 29,960 generated atomic requirements: passed;
- dependency-free Chrome audit: passed EN/LT/RU at all six planned widths with
  zero overflow, unnamed controls, undersized scoped actions, invalid images,
  duplicate IDs, external images, member-shell leakage, or console errors.

## Risks And Stop Conditions

Stop and update the plan before implementation if:

- a proposed claim describes only prototype/session behavior as persisted;
- member home routing would discard an existing required workflow rather than
  redirect to its canonical destination;
- an image or statistic lacks a first-party, maintainable source;
- a design introduces a generic marketing hero that conflicts with the
  Neighborhood Noticeboard product direction;
- guest rendering requires private or viewer-sensitive data;
- the work begins closing forum atomic IDs without selecting exact IDs and
  evidence;
- browser, localization, auth, or full-suite gates reveal a new failure.

## Relationship To The Completion Roadmap

The combined forum/product catalogue currently contains 29,960 atomic
requirements: 1,140 verified and 28,820 open. The join page is an immediate
product-entry package requested by the product owner. Its existence does not
close any forum atom automatically.

Execution order is now:

1. maintain the verified JP-01 through JP-05 entry contract;
2. execute the next exact Phase 3 database-correctness reconciliation package;
3. continue the eight dependency-ordered completion waves in
   `docs/plans/forum-completion-plan.md`;
4. plan the consolidated settings package only after mapping exact open IDs
   across pet, social, content, communication, and global preference phases.

This keeps the public entry point coherent now without hiding the much larger
verified completion backlog.
