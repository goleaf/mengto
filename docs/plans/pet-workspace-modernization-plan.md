# Canonical Pet Workspace Modernization

Date: 2026-08-03

## Scope And Product Decision

`/pets` is the authenticated personal pet workspace. It is not a public pet
directory, a social recommendation feed, or a second discovery system.
Cross-user pet recommendations belong to `/discover?category=pets`; public pet
identity belongs to `pets.profile`; editing and access management belong to
`pets.manage.*`.

## Baseline Audit

The retired route used `PetDirectoryPreviewController` and
`PreviewService::petDirectoryData()`. That service returned six hard-coded pet
arrays, external image URLs, fictional Portland neighborhoods, and session-only
Follow actions. `DirectoryFilter` searched and sorted those PHP arrays. The
page did not read `PetProfile`, current manager records, lifecycle state,
privacy, protected media, invitations, or the canonical create/manage flow.

The repository already contained the correct first-party domain:

- `PetProfile` with typed lifecycle and visibility;
- `PetProfileManager` with role, state, permission overrides and expiry;
- `PetProfilePolicy` and `PetProfileAccess`;
- class-based Livewire create, manage, invitation and public-profile surfaces;
- protected `PetProfileMedia` routes;
- care journals, medical records and discovery deep links;
- EN/LT/RU localization, factories and production-gated demo seeders.

No new pet aggregate, permission system, media store, discovery catalogue, or
application shell was required.

## Canonical Route Contract

| Route | Purpose | Data boundary |
| --- | --- | --- |
| `pets.index` | personal workspace | owned or currently active manager access |
| `pets.manage.create` | private draft creation | active authenticated account |
| `pets.manage.show` | profile management | policy-authorized manager permission |
| `pets.manage.invitations` | proposed shared access | invited account only |
| `pets.profile` | profile detail | canonical profile visibility policy |
| `pets.media.show` | protected primary media | profile and media policy checks |
| `discover.index?category=pets` | cross-user recommendation | public, discoverable, unblocked projection |

## Data And Query Architecture

`PetProfileWorkspaceController` authorizes `viewAny`, delegates URL validation
to `BrowsePetProfilesRequest`, and delegates reads to `PetProfileWorkspace`.
The service starts from `PetProfile::managedBy($user)`, selects only card
columns, eagerly loads only the current active manager and primary media asset,
applies database search/filter/sort, and paginates twelve records with the
query string preserved. Pending invitations use a separate bounded count and
never become active profile access.

The presenter returns arrays only. Blade performs no model, service,
authorization, aggregate, route inference, or database work.

## Interface Architecture

The page reuses `x-app-shell`, `x-page-stack`, `x-page-header`,
`x-directory-toolbar`, `x-empty-state`, `x-notice`, `x-action-control`,
`x-directory-card`, `x-card-media`, `x-linked-media`, `x-card-heading`,
`x-status-badge`, and paginator views. The existing pet card gained a strict
workspace presentation mode while retaining its compatibility mode for the
legacy Circle surface.

Workspace cards show only safe operational facts: profile name, species,
breed, age label, lifecycle state, visibility, current viewer role,
discoverability, relative update time, and policy-derived actions. No complete
medical record, exact location, private manager detail, invitation token, or
raw media path enters the projection.

## States

- Initial: bounded owned/shared result list.
- Empty: create the first canonical profile.
- Filtered empty: clear filters without conflating absence of data.
- Pending invitation: review proposed role and permissions separately.
- Draft/private/archived/lost: typed status and visibility labels.
- Missing media: linked, named pet placeholder with stable geometry.
- Pagination: twelve cards per page with preserved URL state.
- Invalid URL state: Form Request rejection and safe redirect.
- Inactive account: portal boundary logout and unavailable-account feedback.

## Localization And Accessibility

The dedicated `pet_workspace.php` catalogue exists in EN, LT and RU. Names,
states, roles, visibility, age, counts and relative time reuse existing typed
enum translations and `LocaleFormatter`. The page has one `h1`, named search,
filter and sort controls, text plus icon status, useful media alternatives,
44-pixel mobile targets, reduced-motion inheritance, no horizontal overflow,
and no color-only state.

## Factories And Demo World

`PetProfileFactory` now has explicit private, draft, discoverable, archived and
lost states; private and archived states cannot remain discoverable. The
canonical demo gives Scout and Nori real birth dates and gives Mia one expiring
caregiver invitation to the discoverable Meta profile. The invitation seeder
uses `updateOrCreate`, remains environment-gated through the existing discovery
demo guard, and was executed twice without duplicate records.

## Test Plan

1. Route resolves to the production workspace controller.
2. Owned and active shared profiles render; invited, suspended and unrelated
   public profiles do not.
3. Search, filters, sorting, invalid state and pagination are server-driven.
4. Empty and filtered-empty states stay distinct.
5. Pending invitations are visible but do not grant access.
6. EN/LT/RU pages render without raw keys.
7. Inactive accounts fail through the portal boundary.
8. Query count remains bounded as profile volume grows.
9. Media and title share the exact policy-derived destination.
10. Loopback Chrome verifies 1440 EN, 375 RU and 320 LT with screenshots,
    touch targets, overflow, landmarks, labels, prototype removal and console.

## Implementation Passes

1. Audit route, service, page, card, model, policy, factory, seeder and tests.
2. Establish `/pets` as personal workspace and preserve discovery ownership.
3. Add validated enums, request, controller, age service and workspace service.
4. Migrate page and card to canonical shared UI.
5. Remove the route-specific preview controller and public preview method.
6. Add localization, factory states and coherent demo data.
7. Add feature, authorization, query and linked-media regressions.
8. Add and run repeatable browser verification.
9. Synchronize portal and interface registries.
10. Run full release gates and publish only the attributable diff.

## Current Evidence

- Targeted PHP gate: 43 tests, 526 assertions.
- Expanded pet/profile/architecture/localization gate: 82 tests, 64,339
  assertions.
- Full serial PHP gate: 2,670 tests, 84,934 assertions.
- Larastan completed with zero errors; Pint completed successfully for every
  attributable PHP file.
- Composer strict validation and dependency audit passed; npm high-severity
  audit reported zero vulnerabilities.
- Isolated fresh migration and production-safe seed completed with 215 tables,
  130 migrations and five stable users before and after a repeat seed.
- Browser gate: 1440 EN, 375 RU and 320 LT passed with two canonical cards,
  one invitation notice, zero horizontal overflow, unnamed controls, duplicate
  IDs, raw keys, private leaks, prototype names, Follow actions and console
  errors.
- Production Vite build passed after correcting the shared select-icon inset.
- Cache compilation and final diff checks are recorded during publication;
  commit and push evidence belong in the delivery report because their
  identifiers do not exist before publication.
