# Accessibility

Target: WCAG 2.1 AA for critical workflows.

## Acceptance Criteria

- Semantic landmarks and one logical page `h1`.
- Sequential heading hierarchy.
- Native controls where possible.
- Every field has a label and field-linked error.
- Icon-only controls have accessible names and tooltips where unfamiliar.
- Visible focus and logical keyboard order.
- Modal focus trap, initial focus, Escape behaviour, and focus restoration.
- Status updates use text and `aria-live` only when appropriate.
- Status is not communicated by color alone.
- Tables retain headers and meaning on small screens.
- Maps/charts have textual or tabular equivalents.
- Meaningful image alt; decorative images use empty alt.
- 44-pixel touch targets for primary compact controls.
- No keyboard trap, hover-only workflow, or horizontal page overflow.
- 200% zoom remains usable.
- Reduced-motion and forced-colors preferences preserve function.
- Drag-and-drop has a keyboard alternative.

## Critical Review Flows

1. Register, login, reset, logout.
2. Create and manage a private medical record.
3. Create/complete a care task.
4. Read a device alert and issue/confirm an allowed command.
5. Create forum content and resolve validation.
6. Browse and filter marketplace, lost/found, and place results.
7. Open/close message call preflight and stop media.
8. Discover a mentor, review match reasons, send a private message, and open
   the report form with its truthfulness acknowledgement.
9. Discover a group, inspect privacy/membership state, respond to an
   invitation, and manage membership through keyboard-accessible controls.
10. Create a journal, add a dated entry and measurements, review a textual
    progress table, comment, export, and archive without drag-only controls.

## Automated And Manual Evidence

Automated browser checks cover semantic names, focus visibility, page overflow,
console errors, and critical interaction. Manual review covers keyboard order,
screen-reader announcements, touch, zoom, reduced motion, and forced colors.

The shared login, registration, password-recovery, password-confirmation, and
email-verification interface was checked at 320x700, 375x812, 768x900,
1024x768, 1440x900, and 1920x1080. The rendered
pages had one `main`, one `h1`, no horizontal overflow, no unnamed buttons, no
unlabelled visible fields, and no action links or buttons below the 44-pixel
target contract. Russian registration fit a 1440x900 viewport without document
overflow; shorter viewports retain normal page scrolling. A rejected Livewire
login announced the localized safe error and restored focus to the email
field. A connected registration-to-email-verification transition uses a full
document navigation and leaves the destination console without duplicate Vite
preload warnings. The current-page console contained no warnings or errors.

The public forum and search directories for lost/found, marketplace, and
experts were also checked at 320 pixels. Forum category/filter links and every
visible search input, select, action, and square clear-filter control meet the
44-pixel touch-target contract without horizontal page overflow.

The mentorship flow was checked at 1440x900 and 375x812 with one logical
heading, no unnamed buttons, no unlabeled controls, no horizontal overflow,
44-pixel primary controls, no raw translation keys, and no browser-console
warnings or errors. A participant-only Livewire message was submitted through
the rendered interface.

The persistent group directory and private workspace were checked at 1440x900
and 375x812. Both had one logical heading, no document overflow, zero unnamed
buttons, zero unlabeled visible controls, zero controls below 44px, and no
console warnings/errors. Private management, owner membership, and pending
invitation content remained inside the authorized view.

The group-content and poll workspace was checked at 1440x900 and 375x812.
Single-, multiple-, and ranked-choice ballots expose native grouped controls,
visible textual state, localized authority notices, and targets at least 44px
high. The page had one logical heading, no horizontal overflow, no unlabeled
controls, and no current-page console errors. A real Livewire vote changed the
button to `Update vote`, exposed the permitted result, and announced the
success status without a full-page reload.

Known tool limitations belong in `docs/known-limitations.md`; a missing tool
does not waive semantic implementation or manual review.

The forum journal UI uses one existing topic heading, sequential section
headings, explicit labels/descriptions, field-linked errors, polite status
feedback, semantic dates, native progress/table alternatives, required image
alt text, action-specific loading, and neutral missed-update language.
A real Lithuanian journal and local-time milestone entry were created through
Livewire. At 375x812 and 1440x900 the rendered topic had one `main`, one `h1`,
zero horizontal overflow, no raw translation keys, no unnamed buttons, no
visible unlabeled controls, three semantic progress elements, and no console
warnings or errors. Exact evidence is recorded in the journal work package.
## Event Workflows

The event directory and workspace provide one logical heading, explicit form
labels, field-associated validation, semantic definition lists and tables,
action-specific loading states, an offline notice, localized confirmations,
non-color status text, and normal link fallbacks. Controls use existing
minimum touch targets and visible focus. Waitlist and capacity information is
available as text, and no event operation requires drag-and-drop, hover, or
animation.

## Expert Session Workflows

The directory and workspace use one logical heading, explicit labels,
field-linked validation, live status output, localized safety disclaimers,
non-color queue states, source-link text, action-specific loading, offline
feedback, visible focus, and mobile-safe touch targets. No session operation
requires drag-and-drop, hover, animation, or a pointer-only gesture.
