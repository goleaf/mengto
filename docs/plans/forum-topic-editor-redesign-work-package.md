# Forum Topic Editor Redesign Work Package

Date: 2026-08-03

Status: implemented and verified

## User Contract

Redesign `/forum/ask` as one coherent authoring surface. Remove the detached
right sidebar, move its complete publishing guidance above the form, and keep
every existing field, validation rule, submission intent, authorization
boundary, and taxonomy interaction intact.

## Problem

The editor used a wide content column beside a narrow fixed-width sidebar.
Publishing guidance was visually detached from the task it governed, while the
long form had no clear progression between question context, response
preferences, and optional media. At smaller widths the sidebar became another
stacked card instead of part of the authoring flow.

## Implementation Contract

1. Render one `data-forum-editor-shell` containing both the guidance and the
   complete form.
2. Place the semantic five-item publishing checklist immediately before the
   form and remove the `forum-sidebar` layout from the editor.
3. Divide the unchanged form controls into three labelled sections: context,
   response, and optional media.
4. Preserve all 23 named inputs, the animal-taxonomy selector, draft and
   publish intents, error summary, CSRF/method handling, autosave key, similar
   topic endpoint, and controller-provided data.
5. Localize every new label and description in English, Lithuanian, and
   Russian.
6. Keep the surface responsive, keyboard-complete, free of horizontal
   overflow, and usable with reduced motion and 44-pixel mobile targets.

## Query Delta

The production query delta is zero. The route, controller, Eloquent loading,
Livewire taxonomy component, policies, validation, and persistence path are
unchanged. The package changes only server-rendered structure, localized copy,
styles, browser assertions, tests, and documentation.

## Acceptance Evidence

- RED contract: the new structural test failed because the unified editor
  shell did not exist.
- Focused contract: 2 tests and 33 assertions passed.
- Related forum, accessibility, page-identity, responsive, localization, and
  architecture slice: 86 of 87 tests passed; the one failure belongs to an
  unrelated uncommitted pet-media route-classification change in the shared
  worktree.
- Isolated `main` plus only this package: the complete sequential suite passed
  with 2,588 tests and 82,043 assertions.
- Full Pint and Larastan passed in the isolated attributable snapshot;
  Larastan reported zero errors.
- Composer strict validation and audit passed, and npm audit reported zero
  vulnerabilities.
- The production Vite build and compiled Blade views passed.
- Isolated Chrome at 1440x900 and 375x812 confirmed one shell, guidance before
  the form, five checklist items, no sidebar, three ordered sections, no
  overflow, raw keys, unnamed controls, undersized mobile targets, invalid
  media, duplicate IDs, or console errors.

## Scope Boundary

This package does not change forum requirements, category taxonomy, schema,
routes, authorization, business logic, or persistence. Unrelated discovery,
pet-media, and place-question work present in the shared worktree is excluded
from the attributable commit.
