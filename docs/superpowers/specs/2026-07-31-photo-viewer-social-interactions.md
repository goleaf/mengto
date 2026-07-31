# Photo Viewer And Per-Photo Social Interactions

## Goal

Every PawCircle image that already offers a full-size view must open through one
responsive viewer with zoom, previous and next navigation, a useful caption,
per-photo reactions, and per-photo comments.

## Library Decision

Use PhotoSwipe `5.4.4`.

- It is MIT licensed for personal and commercial applications.
- It supports responsive `srcset` sources and loads the core only when opened.
- It preserves a normal link to the full-size image as progressive enhancement.
- It supports touch gestures, keyboard navigation, zoom, focus management, and
  custom root-level UI elements.
- Fancybox is not selected because closed commercial applications require a
  commercial license.

## Interaction Contract

- A photo has a stable key based on its owning publication and position.
- Reactions and comments belong to that photo key, never to the publication as
  a whole.
- One signed-in person may select one reaction per photo. Selecting the same
  reaction again removes it; selecting another replaces it.
- A comment is plain text, server validated, escaped on output, and limited to
  1,200 characters.
- Each rendered comment form has a server-validated ULID idempotency key;
  repeated submission by the same member creates one comment.
- Unknown photo keys are rejected server-side.
- Viewing remains public where the underlying page is public. Mutations require
  an authenticated, active account.
- Standard POST forms remain usable without JavaScript. Opening a photo records
  its key in the page URL so a successful POST can reopen the same photo.

## Layout Contract

- Desktop uses a dark image canvas and a fixed paper interaction panel on the
  right.
- Mobile and narrow tablet use the image canvas above a scrollable interaction
  sheet.
- Viewer controls and form controls remain at least 44 pixels high.
- The image keeps its intrinsic ratio and uses responsive sources while zooming.
- The underlying document never gains horizontal overflow.

## Accessibility

- Full-size image links are the no-JavaScript fallback.
- Triggers have explicit accessible names.
- PhotoSwipe owns modal focus trapping, Escape-to-close, arrow navigation, and
  focus restoration.
- The custom interaction panel uses headings, labelled forms, visible focus,
  textual reaction labels, semantic status text, and escaped comments.
- Reduced-motion and forced-colors preferences remain usable.

## State And Security

`PhotoAsset` provides a relational identity for a server-catalogue photo.
`PhotoReaction` enforces one reaction per member and photo with a database
unique constraint; `PhotoComment` retains shared plain-text discussion and its
accountable author. Browser photo keys and actions are untrusted. A dedicated
Form Request validates shape, policies authorize mutations, and an Action
resolves the photo against the server-side feed before every mutation.

The presenter batch-loads bounded photo assets, aggregate reaction counts,
selected reactions, the latest 40 comments per photo, and comment authors.
This makes shared interaction visible to every viewer without a query per
photo. The database retains all comments even when presentation is bounded.

## Verification

- Feature tests cover viewer markup, responsive dimensions and sources,
  per-photo reaction isolation and replacement, shared member reactions,
  shared comment visibility, escaping, unknown photos, authentication, a
  bounded query count, and localization.
- Browser checks cover `320`, `375`, `768`, `1024`, `1280`, `1440`, and `1920`
  pixel widths, zoom, previous and next navigation, form submission, focus,
  overflow, and console errors.
- Run targeted Pest, Pint, Larastan, localization checks, the Vite production
  build, and the full Pest suite.
