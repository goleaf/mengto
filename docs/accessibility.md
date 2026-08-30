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
- Linked representative media has a localized accessible name, visible focus,
  and the exact same destination as its adjacent resource link.

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
11. Open a representative pet, group, expert, message participant, or listing
    through either its image/placeholder or adjacent title and reach the same
    authorized destination.

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

Every authentication password field exposes an independent native visibility
button with a 44-pixel target, exact `aria-controls`, localized changing name,
and pressed state. The masked input remains the no-JavaScript baseline; the
Lucide eye icons are decorative because the button owns the accessible name.

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

## Linked Representative Media

Linked media uses an ordinary anchor with a localized `aria-label`; it does
not emulate navigation with JavaScript. The adjacent text link remains
available so the interface does not force a visual-media interaction. Both
affordances share the presenter's canonical URL, and a missing or unauthorized
target renders passively. Focus uses the shared visible ring in normal and
forced-colors modes, hover motion is disabled when reduced motion is
requested, and the component cannot wrap buttons, links, inputs, or other
interactive descendants.

Connected browser evidence covered `/pets`, `/groups`, `/discover`, and
`/messages?conversation=ari` at 320, 375, 768, 1024, 1440, and 1920 pixel
widths. All 24 route/viewport combinations had zero horizontal overflow, zero
unnamed linked-media controls, zero nested interactive descendants, and zero
console warnings or errors. Pointer and Enter-key activation of Scout's image
reached the same canonical profile URL as the title, and mobile focus exposed
the localized accessible name and visible focus ring.

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

## Topic Lifecycle Workflow

The lifecycle panel uses the topic's existing H1, a semantic status/history
region, native details/buttons/forms, explicit labels, an error summary,
polite status output, text-plus-icon warnings, action-specific loading, and an
offline notice. Destructive actions use localized native confirmation.

Playwright verified the public history and stale warning at desktop width and
at 375x812. The mobile document and lifecycle region had no horizontal
overflow; the current navigation had no console warning or error. Redirect
navigation also preserved the canonical destination. No lifecycle operation
depends on hover, drag-and-drop, animation, or pointer-only input.

## Forum Accessibility Package

Source-section 71 (`forum.interface.0010` through `.0033`) is implemented as
one cross-cutting forum contract:

- one reusable, focusable, assertive error summary presents every validation
  problem and links each keyed message to its native control with
  `aria-invalid` and `aria-describedby`;
- the adapter is idempotent across Livewire DOM updates and
  `livewire:navigated`, and no business decision is made in JavaScript;
- new topic images and videos require a meaningful localized description;
  video also requires a bounded transcript and may include a
  content-validated WebVTT caption track with a supported locale;
- generated storage names, escaped presentation, compensation cleanup, and a
  localized legacy-media fallback protect old and new records;
- every forum data table has a caption and scoped column headers;
- native pagination, disclosures, controls, upload inputs, and the existing
  ordered text alternatives for maps remain the primary semantic interface;
- no forum workflow uses a custom dialog or requires drag-and-drop;
- forum controls use the 44-pixel target token, statuses retain text/icons,
  and the critical muted-text pair is 4.91:1.

`npm run test:browser:a11y` launches an isolated headless system Chrome through
the DevTools protocol without adding a browser framework dependency. Against
the seeded temporary database it verified 1440x900, 375x812, and 320-pixel
reflow: one `main` and `h1`, no horizontal overflow, unnamed controls,
duplicate IDs, missing image alternatives, invalid tables, undersized forum
controls, or console errors. Keyboard Tab exposed the skip link and its focus
ring. A real administrator Livewire login followed by an invalid topic submit
focused the complete summary and associated the title field with its error.
The authorized administration table exposed its caption and scoped headers.

`npm run test:browser:animal-science` uses the same disposable boundary but
stops after the category-25 matrix. It verifies desktop English, mobile
Lithuanian, 320-pixel English reflow, and Russian forced colors with the exact
54-child hierarchy, localized root copy, fixed product typography, focus,
touch-target, overflow, translation-key, and console contracts.

Screenshots and the JSON report are written to an operating-system temporary
directory and removed after the check. The package command creates its own
temporary SQLite database and loopback server; set `CHROME_BIN` only when
Chrome is not in a detected platform path. The underlying Node runner refuses
non-loopback URLs and refuses to use demo credentials without the wrapper's
explicit mutation consent. When the runner itself executes as root in an
isolated CI/container environment, it supplies Chromium's required
`--no-sandbox` launch flag; non-root runs retain the browser sandbox.
