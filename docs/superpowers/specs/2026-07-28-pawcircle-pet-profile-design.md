# PawCircle Pet Profile Design

## Status

Approved direction, awaiting review of this written specification.

The current project directory has no `.git` metadata, so this document cannot
be committed until the directory becomes a Git repository.

## Goal

Add a second static PawCircle screen for Scout, Mia Carter's dog. The screen
should make the existing feed feel like part of a coherent social product while
remaining a presentation-only Blade prototype.

## Scope

The new page is available at `/pets/scout` through a grouped, named web route.
It presents Scout's identity, care information, owner, photos, and recent
moments. The existing home feed links Scout's name to this page.

This slice includes:

- A responsive pet profile page.
- Shared static preview data used by both the home feed and profile page.
- Reusable Blade components for profile-specific sections.
- A plain navigation link from the current pet card to Scout's profile.
- Regression coverage for the existing home feed.

This slice excludes:

- Database tables, Eloquent models, migrations, factories, or seeders.
- Authentication, authorization, follows, reactions, messages, and comments.
- Forms, validation, search, filters, uploads, or JavaScript state.
- Dynamic pet slugs, profile editing, and a general pet directory.
- Filament resources or admin screens.

## User Experience

### Desktop

The profile starts with a wide visual header containing Scout's photograph,
name, breed, age, location, and current social status. Beneath it, the page uses
a two-column layout:

- The main column contains Scout's story, photo gallery, and recent moments.
- The supporting column contains care facts, social compatibility, and Mia's
  owner card.

The layout stays aligned with the existing PawCircle content width, palette,
typography, border treatment, and spacing rhythm.

### Mobile

At narrow widths, the page becomes a single reading flow:

1. Profile identity.
2. About Scout.
3. Care facts and compatibility.
4. Owner.
5. Gallery.
6. Recent moments.
7. Existing mobile navigation.

No content may overlap, require horizontal scrolling, or become hidden behind
navigation.

### Preview States

The profile has no active social actions. Any action shown for visual context
must be a disabled native button with `aria-disabled="true"`. Navigation between
the home feed and Scout's profile is active because it is part of the static
prototype's information architecture.

All collections use `@forelse` and provide an explicit empty state. Images have
descriptive alternative text, and heading levels remain sequential.

## Content Model

Scout's profile data contains:

- Identity: name, species, breed, age, location, profile image, and cover image.
- Status: availability for park walks.
- Story: a short owner-written introduction.
- Care facts: energy level, size, preferred walk duration, vaccination status,
  and dietary note.
- Compatibility: friendly with dogs, calm around older children, and cautious
  around cats.
- Gallery: three to four static images with meaningful captions and alt text.
- Recent moments: selected static posts rendered through the existing feed-card
  presentation where practical.
- Owner: Mia Carter's existing avatar, location, and summary.

All data is prepared in PHP before rendering. Blade templates only display the
provided values.

## Architecture

### Routes

Keep the existing `web` middleware and `pet-social.` name group. Add one fixed
route:

- Path: `/pets/scout`
- Name: `pet-social.pets.scout`
- Controller: `PetProfilePreviewController`

A fixed route is intentional. It avoids pretending that dynamic records or
route model binding exist before the prototype gains a persistence layer.

### Controllers And Data

`PetSocialPreviewController` and `PetProfilePreviewController` remain invokable
and thin. Shared static content moves from controller-private methods into one
dedicated `App\Services\PawCirclePreviewService`. The service exposes
`homePageData()` and `scoutProfileData()` methods so each controller receives a
complete view-data array without assembling content itself.

The service performs no queries, I/O, caching, or business operations. Its only
responsibility is preparing consistent demonstration content for Blade.

### Blade Structure

The current app shell becomes page-layout agnostic while retaining the shared
document head, top navigation, content width, and mobile navigation. The home
page owns its three-column feed layout; the profile page owns its two-column
profile layout.

Expected reusable components:

- `pet-profile-hero`: identity and primary image.
- `pet-facts`: care and compatibility facts.
- `pet-gallery`: responsive image layout and empty state.
- `owner-summary`: Mia's compact owner presentation.
- Existing `feed-card`: all recent moments, using the same post data contract as
  the home feed.

Components receive explicit props, merge attribute classes, and contain no
queries or business rules.

## Error And Empty States

The fixed Scout route has no invalid-slug state. Standard Laravel 404 handling
continues to cover all other paths.

Gallery and recent-moment collections render clear empty states through
`@forelse`. Remote image failure must not collapse the layout because image
containers use stable aspect ratios and dimensions.

## Testing Strategy

Implementation follows red-green-refactor:

1. Add a focused Pest feature test for the new named route and page contract.
2. Run it and confirm it fails because the route does not exist.
3. Add the smallest route, controller, data, and Blade implementation that makes
   it pass.
4. Refactor shared page data and layout while keeping both feature tests green.

The new test verifies:

- The named profile route exists and returns a successful response.
- Scout's name and profile-specific sections are visible.
- The page contains gallery, care, owner, and recent-moment sections.
- Preview actions remain disabled.
- No form is rendered.
- The home page contains a link to the named Scout profile route.

Final verification:

- `php artisan test --compact --filter=PetProfilePreviewTest`
- `php artisan test --compact`
- `vendor/bin/pint --dirty --format agent`
- `npm run build`
- Browser checks at 320, 768, 1024, and 1440 pixels.
- Browser console and page layout checks for errors and overlap.

## Query Delta

Before: zero application queries for the static home preview.

After: zero application queries for both static preview pages.

No schema inspection or query-plan analysis is required because this slice does
not access a database.

## Acceptance Criteria

- `/pets/scout` renders a polished, responsive Scout profile.
- The route is grouped and named `pet-social.pets.scout`.
- The existing feed links to Scout's profile and remains visually intact.
- Controllers contain no duplicated preview-content definitions.
- Blade contains no queries, business logic, active forms, or active social
  controls.
- Empty collection states are present.
- Targeted and full tests pass.
- Pint and the Vite production build pass.
- Desktop and mobile browser checks show no overlap or console errors.

## Future Roadmap

Later static slices should be designed and approved independently:

1. Pet directory and discovery cards.
2. Meetup detail and group detail pages.
3. Lost and Found board.
4. Care reminders and health timeline.
5. Only after the prototype is approved: persistence, authentication, policies,
   Form Requests, and real social interactions.
