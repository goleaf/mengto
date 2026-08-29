# Authentication Password Visibility Toggle

## Goal

Add one consistent show/hide control to every password field in the PawCircle
account-entry experience. The control must work on registration, login,
password reset, and fresh-password confirmation without changing the existing
server-authoritative authentication flow.

## Scope

The shared `auth-field` Blade component automatically enhances fields whose
declared type is `password`. This covers:

- `login-password`;
- `register-password` and `register-password-confirmation`;
- `reset-password` and `reset-password-confirmation`;
- `confirm-password`.

Email, name, and other non-password fields remain unchanged. Future auth forms
using the same component and `type="password"` inherit the control.

## Interaction Contract

- Every password starts masked with a static `type="password"` fallback.
- A button inside the field's trailing edge toggles only that field between
  `password` and `text`.
- The initial icon is Lucide `eye`; the visible-password state uses
  `eye-off`.
- The button reports `aria-pressed`, controls the exact input through
  `aria-controls`, and changes its localized accessible name between show and
  hide.
- Toggling visibility does not clear, copy, submit, persist, or send the value
  to the server.
- Validation, autocomplete, focus restoration, Livewire binding, and form
  submission semantics remain unchanged.

## Presentation And Accessibility

- Alpine supplied by Livewire owns only the local ephemeral visibility state;
  no new JavaScript package or standalone listener is added.
- The toggle is a native `type="button"` control with a 44-by-44-pixel target,
  visible focus, hover-independent operation, and forced-colors support.
- The input receives enough trailing padding that text never sits beneath the
  control.
- The static masked input remains fully usable before Alpine initializes.
- English, Lithuanian, and Russian use matching stable translation keys for
  `Show password` and `Hide password`.

## Verification

- A focused Pest feature test proves the exact toggle contract on all six
  password inputs and proves ordinary auth fields do not receive it.
- Localization and architecture tests prove locale parity and passive Blade.
- The auth feature suite proves the existing server flows remain intact.
- Pint, Larastan, Blade compilation, and the production Vite build verify the
  affected runtime boundaries.
- Connected page smokes verify `/register`, `/login`, password reset, and
  password confirmation render without server or asset errors.

## Non-Goals

- No password-strength meter, password generation, clipboard action, or
  browser persistence.
- No change to password rules, authentication errors, rate limits, or account
  authorization.
- No replacement of the existing SCSS auth component layer.
