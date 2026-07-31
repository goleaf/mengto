# Authentication Interface Redesign

## Goal

Replace the isolated centered authentication card with one coherent,
production-ready account-access system for login, registration, password
recovery, password confirmation, and email verification. The redesign must
retain the existing server-authoritative Livewire operations while making the
critical authentication path calm, distinctive, localized, accessible, and
usable from 320-pixel mobile through wide desktop.

## Visual Direction

The interface follows the PawCircle neighborhood-noticeboard system: warm
cream and paper surfaces, charcoal text, the existing leaf green as the only
interactive accent, low-contrast borders, modest radii, flat tonal layers,
Instrument Sans, and restrained decorative pet cues. Desktop uses a split
composition with a contextual trust panel and a focused form workspace.
Mobile places a compact brand story first and the form immediately after it.

The design intentionally avoids stock imagery, generic gradient-heavy SaaS
cards, excessive shadows, oversized radii, and decorative motion.

## Shared Components

- `components.auth-layout` owns the responsive story/workspace composition,
  skip link, offline status, and protected-account context.
- `auth-field` owns explicit labels, required state, autocomplete,
  field-linked help, and field-linked validation feedback.
- `auth-page-header`, `auth-status`, `auth-submit`, and `auth-switch-link` own
  repeated page copy, state, actions, and account-flow navigation.
- `resources/scss/_auth.scss` owns the mobile-first layout, states, focus,
  forced-colors support, and 44-pixel interaction contract.

Blade remains passive. Livewire forms continue to own browser state and the
existing Actions continue to own authentication and registration mutations.
Links between account-access pages and redirects after account mutations use
ordinary full-document navigation to avoid duplicate Vite preload insertion;
this does not weaken the server-authoritative Livewire mutation boundary.
Language and time zone remain absent from registration and stay in protected
profile settings.

## Responsive And Accessibility Contract

- no horizontal page overflow at 320, 375, 768, 1024, 1440, or 1920 pixels;
- one `main` landmark and one logical `h1` on each account page;
- explicit visible labels and browser autocomplete semantics for every field;
- field-linked localized validation messages and focus restoration after
  rejected login or password-confirmation attempts;
- visible keyboard focus, textual offline/loading/success states, and no
  hover-only action;
- primary controls and compact action links expose at least a 44-pixel target;
- content may scroll vertically when a long localized registration form does
  not fit a short viewport, but the page never creates an inner form trap;
- reduced-motion remains functional and forced-colors preserves native
  boundaries and focus.

## Verification Evidence

- focused auth and localization Pest suites;
- production Vite build;
- connected browser geometry and accessibility checks at 320, 375, 768,
  1024, 1440, and 1920 pixels;
- visual screenshot review of Russian login and registration on mobile and
  desktop;
- a real rejected Livewire login confirmed localized feedback and returned
  focus to the email field;
- a real registration-to-email-verification redirect completed as a full
  document navigation without duplicate Vite preload warnings;
- browser checks confirmed no registration language/time-zone controls and no
  current-page console warnings or errors.
