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

## Automated And Manual Evidence

Automated browser checks cover semantic names, focus visibility, page overflow,
console errors, and critical interaction. Manual review covers keyboard order,
screen-reader announcements, touch, zoom, reduced motion, and forced colors.

Known tool limitations belong in `docs/known-limitations.md`; a missing tool
does not waive semantic implementation or manual review.
