# Authenticated Portal Access Master Plan

Status: implemented and verified on 2026-08-03

Plan date: 2026-08-03

## Product Decision

PawCircle is now a private authenticated portal. Anonymous visitors may use
only the minimum account-entry infrastructure required to authenticate or
recover access. They may not browse product pages, inspect public profiles,
open token shares, submit sightings, download files, print/export content, or
invoke product mutations.

This decision intentionally supersedes earlier public-discovery and guest join
behavior. Existing authorization policies remain mandatory after login; portal
authentication is an outer boundary, not a replacement for owner, role,
visibility, verification, or resource policies.

## Anonymous Allowlist

The complete application-level guest allowlist is:

- `login`;
- `register`;
- `password.request`;
- `password.reset`;
- `locale.update` for the localized account-entry shell;
- the Livewire update endpoint only as transport for those four guest auth
  components.

Framework assets and `/up` may remain anonymously reachable because they
contain no product data and are required for rendering or infrastructure
health. Livewire upload and preview endpoints are not guest endpoints.

Email verification, password confirmation, logout, and every product route
require an authenticated active account. Unverified active accounts may access
only verification, password-confirmation, and logout flows; product access
redirects to the verification notice.

## Baseline Audit

The pre-change Laravel route inventory contained 174 routes. The verified
post-change inventory contains 173 because two local-storage routes were
removed and one authenticated media route was added. The baseline split was:

- 106 already carry Laravel authentication middleware;
- 58 web routes do not carry authentication explicitly;
- 10 are framework transport, asset, health, development, or local-storage
  routes outside the application web group.

The exposed web routes include the home/join page, content feed, discover,
groups, meetups, places, forum pages and media, knowledge pages and exports,
neighbors, pets and profiles, posts, experts, marketplace, lost/found,
temporary medical/care/device shares, and legacy redirects. One lost/found
sighting mutation and one care-share entry mutation are intentionally guest
capable today and must become authenticated-only.

The local filesystem currently registers a direct `storage/{path}` route
because private local serving is enabled. A future `storage:link` could also
expose files stored on the public disk outside Laravel middleware. Both paths
must fail closed.

## Architecture

### Central portal boundary

Add one `RequirePortalAccess` middleware to the `web` group after session
startup and before route-model binding. It must:

1. allow only the exact guest route-name allowlist;
2. redirect anonymous HTML requests to `login` while preserving the intended
   destination;
3. return the normal unauthenticated response for JSON requests without
   exposing route or model existence;
4. terminate blocked or suspended sessions through the existing unavailable
   account response;
5. redirect unverified active accounts to `verification.notice` except for
   verification, password-confirmation, and logout routes;
6. run before implicit model binding so guests cannot distinguish missing from
   existing resource identifiers;
7. be registered as persistent Livewire middleware so updates retain the
   original route access boundary.

Existing route-specific `auth`, `active`, `verified`, throttling, password
confirmation, policies, and scoped bindings remain in place as defense in
depth.

### Direct files

- Disable Laravel's direct local-disk serving route.
- Do not create a public storage symlink as a deployment contract.
- Replace generated `/storage/...` product URLs with an authenticated,
  path-contained media response before claiming the entire media boundary
  complete.
- Continue using owner-derived private download Actions for medical, care,
  device, group, and forum-journal files.
- Temporary share tokens remain an additional scope/expiry check but no longer
  substitute for an authenticated account.

### Future routes

Architecture tests must fail when a new first-party web route is outside the
portal middleware or when the anonymous allowlist grows without updating this
plan and its security tests. Future API routes must require an authenticated
guard unless an explicitly documented machine-authentication boundary exists.

## Execution Phases

### Phase A — route and session boundary

- add the central middleware and priority ordering;
- persist it across Livewire updates;
- disable direct local serving;
- redirect `/` and every current product GET to account entry for guests;
- protect current guest POST/PUT/PATCH/DELETE endpoints before controller or
  model binding executes.

### Phase B — direct public media contraction

- inventory every public-disk writer and rendered URL;
- introduce an authenticated media route with canonical path containment,
  bounded content types, inline/download headers, and no directory guessing;
- convert forum topic media, lost/found photos/video, and marketplace media to
  the authenticated route without changing stored database identities;
- remove public symlink generation and document deployment cleanup;
- test traversal, absolute paths, cross-domain paths, symlink escape, missing
  files, and unauthenticated direct requests.

### Phase C — guest-share revocation

- require portal access before medical, care, and device token resolution;
- preserve token hash, expiry, purpose, view count, revocation, and audit rules
  as secondary authorization;
- ensure a logged-in account still cannot use an invalid, expired, revoked, or
  over-limit token;
- update all formerly public share copy and documentation.

### Phase D — route and action audit

- exercise every first-party named GET route as a guest without allowing model
  lookup or product rendering;
- exercise every first-party mutation verb as a guest without persistence,
  files, events, notifications, audit entries, or counters;
- verify all Livewire product components retain server-side authorization on
  direct action invocation;
- inspect signed, redirect, print, export, poster, file, legacy, and temporary
  URL routes separately.

### Phase E — product and documentation reconciliation

- mark the old public join/discovery contract superseded without deleting its
  useful history;
- update product, security, privacy, authorization, frontend, testing,
  deployment, operations, sitemap/robots, route/cache, progress, and changelog
  documentation;
- map exact atomic requirements before changing generated forum evidence;
- ensure no SEO metadata, sitemap, notification, email, or share message
  advertises anonymous product access.

### Phase F — full release gates

- focused auth-boundary and file tests;
- complete auth, authorization, private-file, Livewire, and architecture
  regression slices;
- Pint and Larastan;
- full sequential Pest suite;
- isolated fresh migration and repeated seed;
- Composer and npm audits plus Vite build;
- route, config, event, and view cache smoke checks;
- real browser checks for guest redirect, intended login return, EN/LT/RU auth
  shell, unverified redirect, authenticated navigation, direct media, mobile,
  keyboard, console, and redirect-loop behavior;
- final route-list allowlist, secret, generated-diff, and Git review before a
  direct `main` commit and push.

## RED Acceptance Contract

The first implementation slice is complete only when tests prove:

1. `/`, representative directories, details, legacy redirects, exports,
   posters, token shares, and media redirect a guest to login;
2. representative guest mutations redirect without changing the database or
   filesystem;
3. authentication and recovery pages still render and submit through
   Livewire;
4. an intended product URL resumes only after successful login;
5. inactive accounts are logged out and unverified accounts cannot enter the
   product;
6. guest requests are rejected before route-model binding;
7. all authenticated existing behavior remains covered by its original tests;
8. route inspection finds no first-party product exception outside the exact
   allowlist.

## Stop Conditions

Do not claim completion if any direct media URL bypasses Laravel, a guest token
still reveals product data, a Livewire product mutation can run without the
original route middleware, an unverified/inactive account reaches product
content, login/recovery loops, or a broad test is merely rewritten to accept a
security regression. Every discovered exception must be fixed or recorded as
an explicit blocker with its exact route and exposure.

## Verification Result

All six execution phases passed on 2026-08-03. The final release evidence is:

- 2,092 serial Pest tests and 73,983 assertions in 111.749 seconds;
- Pint and Larastan with zero errors;
- strict Composer validation, locked Composer audit, npm audit, and the
  production Vite build;
- 111 fresh SQLite migrations, 191 tables, and an idempotent repeated seed
  preserving five users;
- route, configuration, event, and view cache smoke checks;
- immutable forum-source and generated 29,960-requirement checks;
- authenticated Chrome flows plus the EN/LT/RU account-entry shell at 320,
  375, 768, 1024, 1440, and 1920 pixels with no console errors.

The only anonymous HTTP surface is the account-entry allowlist above plus
framework assets and the health endpoint. Existing deployment installations
must remove a legacy `public/storage` symlink if one was created before this
boundary; current configuration does not create or serve it.

## 2026-08-30 Onboarding Lifecycle Addendum

The authenticated portal boundary now includes the persisted onboarding
aggregate without broadening anonymous access or replacing resource policies.
The canonical enabled-verification flow is:

```text
REGISTER / LOGIN
       |
       v
ACCOUNT ACTIVE? -- no --> unavailable-account response
       |
      yes
       v
VERIFICATION REQUIRED AND PENDING? -- yes --> verification notice
       |
      no
       v
ONBOARDING COMPLETE? -- no --> onboarding
       |
      yes
       v
SAFE INTENDED PRODUCT ROUTE OR HOME
```

With verification disabled, registration atomically stamps the account as
verified and enters onboarding directly. Verification and onboarding remain
independent persisted facts; migrated/legacy accounts may be onboarding-ready
without an email timestamp, and configured verification still decides whether
that timestamp gates portal entry.

The incomplete-account route allowlist is exact:

- `onboarding.show`, `password.confirm`, `verification.notice`,
  `verification.verify`, `logout`, and the persistent Livewire update route;
- `pets.manage.create`, `livewire.upload-file`, and
  `livewire.preview-file` only while the persisted current step is
  `pet-relationship`.

All other safe HTML product requests redirect to onboarding without replacing
the first stored product destination. JSON and unsafe mutation requests return
a localized `409` lifecycle response. A safe intended URL is consumed only by
a completed/legacy account and must resolve to an existing same-origin GET
product route.
