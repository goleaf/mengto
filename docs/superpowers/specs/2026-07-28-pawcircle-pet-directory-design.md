# PawCircle Pet Directory Design

## Status

Approved by delegated user direction: proceed without further questions and
make implementation decisions independently.

The project directory has no Git metadata, so this specification cannot be
committed.

## Goal

Add a static pet discovery screen at `/pets` that makes PawCircle feel like a
coherent social product while keeping the prototype presentation-only.

## Experience

The screen opens directly on the directory experience rather than a marketing
hero. A compact page heading reports the local area and visible companion
count. Beneath it, a quiet filter toolbar shows the intended future controls
without pretending that filtering works.

The pet collection is image-led:

- One column at 320 pixels.
- Two columns from the small breakpoint.
- Three columns at wide desktop widths.
- Stable 4:3 media areas prevent layout shifts.
- Card content includes identity, owner, location, availability, and traits.
- Scout links to the existing profile. Other cards remain informational.

The shared shell exposes active Feed and Pets navigation on desktop and mobile.
The mobile navigation stays fixed to the viewport bottom while page content
reserves enough space to remain unobscured. Meet and Tips remain disabled
preview controls.

## Static Controls

The search field, species filters, sort control, and follow controls are native
disabled controls with `aria-disabled="true"` where applicable. No form,
request input, JavaScript state, or URL query parameter is introduced.

Navigation links are active because moving between existing preview pages is
part of the information architecture.

## Architecture

### Route

- Path: `/pets`
- Name: `pet-social.pets.index`
- Controller: `PetDirectoryPreviewController`

The route stays inside the existing middleware, URI-prefix, and name-prefix
group.

### Data

`PawCirclePreviewService::petDirectoryData()` returns the owner, directory
summary, filter labels, and six pet cards. It performs no queries or I/O.

Each card has:

- name, species, breed, age
- owner and neighborhood
- status and two traits
- image and descriptive alternative text
- optional named profile route

### Blade

The directory page lives at `resources/views/pet-social/pets/index.blade.php`.
One reusable `pet-directory-card` anonymous component owns card presentation.
The existing app shell receives an `activeSection` prop for navigation state.

All collections use `@forelse`, all component props are explicit, and no Blade
template performs data access.

## Visual System

The design keeps the existing cream paper, charcoal ink, green accent, coral
status text, Instrument Sans typography, restrained borders, and maximum
eight-pixel card radius.

Photography leads each repeated card, but the page remains a compact directory,
not a landing page. There are no gradients, decorative blobs, nested cards,
oversized type, or animated effects.

## Accessibility

- One `h1` introduces the directory.
- Pet names use `h2`.
- Images have descriptive alt text.
- Active navigation uses `aria-current="page"`.
- Disabled controls use native `disabled`.
- Keyboard focus remains visible on links.
- Empty directory content has an explicit status message.

## Testing

The feature test verifies:

- the named route exists and returns successfully
- the directory sections and six named pets render
- Scout links to the existing profile
- filters are disabled and no form is present
- descriptive image alt text is rendered
- Feed and Pets navigation links appear across existing pages
- the page title and heading hierarchy are correct

Final checks include the focused Pest test, full Pest suite, Pint, Vite build,
route inspection, prohibited-pattern audit, and browser checks at 320, 768,
1024, and 1440 pixels.

## Query Delta

Before: zero feature-owned data queries for the existing PawCircle preview
pages.

After: zero feature-owned data queries for the feed, directory, and Scout
profile. The database-backed session middleware remains an unchanged framework
baseline and is excluded from this feature delta.

## Out Of Scope

- Database schema, Eloquent models, migrations, factories, or seeders
- Real filtering, sorting, pagination, search, or following
- Dynamic pet slugs and additional profile routes
- Authentication, authorization, messages, reactions, and comments
- Filament resources or administration
