# Tailwind CSS 4 Design-System Modernization

## Approved direction

The direct user instruction approves immediate implementation without a
question/approval pause. PawCircle keeps its deliberate Neighborhood
Noticeboard identity: Instrument Sans, cream/paper surfaces, charcoal ink,
leaf-green action/focus, restrained coral attention, modest radii and flat
depth. The modernization changes implementation consistency and resilience,
not the product's visual personality.

## Architecture

Tailwind 4.3.3 remains the utility, token, source-detection, responsive and
state-variant layer through `@tailwindcss/vite`. The entry uses
`@import "tailwindcss" source(none)` and an explicit, reviewed `@source`
registry. Project utilities derive from CSS-first `@theme`, `@utility`,
`@custom-variant`, and narrowly justified `@variant` rules. There is no legacy
JavaScript Tailwind configuration to delete. The mature semantic SCSS layer is
retained and may consume equivalent CSS custom properties; selectors migrate
only when a focused test and browser evidence prove a safe replacement.

## Token model

The theme exposes intentional semantic families for brand, neutrals, status,
page, surfaces, borders, text and focus; typography sizes/leading/weights;
spacing and containers; breakpoints; radii; shadows; z-index; durations,
easing, transitions and restrained animations. Existing brand hex values stay
canonical. Modern OKLCH is used for additive semantic state ramps only after
contrast checks; no color-space conversion may visually drift the brand.

## Source and class safety

All first-party Blade, class-based Livewire/PHP presentation maps, JavaScript,
Laravel pagination and Livewire pagination templates that can emit utilities
are registered. Dynamic fragments are prohibited. Browser-controlled or
domain state maps only to complete allow-listed classes. Rare classes that
cannot live in scanned source use the smallest documented `@source inline()`
range; broad safelists are prohibited.

## Responsive and accessible behavior

Base behavior targets 320px, then progresses through 375, 768, 1024, 1280,
1440 and 1920px. Bounded local scrolling is allowed for named tables/filter
rails while page overflow is not. Long EN/LT/RU content wraps without hiding
actions. Container queries, logical properties, ARIA/data/group/peer/has/not
variants, viewport units, scrollbar utilities and pointer/hover variants are
used only where a confirmed component benefits. Visible focus, 44px product
targets, reduced motion and forced colors are non-negotiable. Decorative text
shadows, masks, zoom and view transitions remain out unless a real product
need and browser evidence emerge.

## Verification

Architecture tests ratchet the CSS-first import, exact source coverage, token
families, absence of dynamic fragments and intentional feature matrix. The
production build is inspected for required selectors and size regression.
Existing isolated browser infrastructure covers responsive geometry, long
locales, keyboard focus, motion/forced-color preferences and console output.
Independent reviewers receive the frozen attributable diff and observed
command ledger before publication.

