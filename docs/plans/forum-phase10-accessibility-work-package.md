# Forum Phase 10 Accessibility Work Package

Last updated: 2026-07-31.

Status: implemented and verified for all 24 selected IDs.

## Contract

This package implements source section 71 without changing forum business
behavior or inventing unused controls. It covers:

- `forum.interface.0010` through `forum.interface.0033`.

The source prompt, master requirements, deterministic requirements matrix,
current progress, root instructions, accessibility guidance, interface code,
and current worktree were reread before this plan was written. The package
must end with file, test, browser, and documentation evidence for every
selected requirement.

## Current Implementation Analysis

- The application shell already provides a localized skip link, one main
  landmark, a focusable main target, viewport configuration, and
  mobile-first layout constraints.
- The design system already defines a 2.75rem touch target, visible focus
  treatment, global reduced-motion behavior, and forced-colors focus support.
- Forum controls use native labels and buttons extensively, but validation
  summaries are duplicated and are not consistently focusable. Inline errors
  are not consistently associated with their controls.
- Forum administration tables use `thead` and scoped column headers, but do
  not expose a table name through a caption or equivalent accessible name.
- Forum images accept an optional description. Forum videos expose the same
  optional description as an accessible name but have no caption track or
  transcript.
- The location map already includes a screen-reader-only ordered textual
  alternative.
- The forum currently has no custom dialog, drag-and-drop sorter, or
  drag-only upload path. Native forms and disclosure elements remain the
  preferred implementation.
- Laravel pagination markup is semantic; forum-specific pagination controls
  already meet the shared touch-target token.

## Desired Result

Representative public and protected forum workflows must remain operable and
understandable by keyboard, screen reader, touch, and zoom users. Validation
must identify and focus a complete error summary and associate inline errors
with the affected controls. Data tables must have accessible names. New topic
media must have meaningful text alternatives, and uploaded video must include
a transcript while supporting a validated WebVTT caption track. Existing
records must render through a safe localized compatibility fallback.

## Affected Files

Expected production modifications:

- `app/Actions/PrepareTopicData.php`
- `app/Actions/CreateTopic.php`
- `app/Actions/UpdateTopic.php`
- `app/Data/PreparedTopicData.php`
- `app/Rules/ValidWebVtt.php`
- `app/Http/Requests/StoreTopicRequest.php`
- `app/Services/ForumPresenter.php`
- `resources/js/app.js`
- `resources/js/forum-accessibility.js`
- `resources/scss/_forum.scss`
- `resources/views/components/forum-error-summary.blade.php`
- `resources/views/forum/editor.blade.php`
- `resources/views/forum/show.blade.php`
- `resources/views/livewire/forum/*.blade.php` where duplicated summaries or
  unnamed tables are present
- `lang/en/forum_accessibility.php`
- `lang/lt/forum_accessibility.php`
- `lang/ru/forum_accessibility.php`
- `scripts/accessibility-browser-check.mjs`

Expected test modifications or additions:

- `tests/Feature/Forum/ForumAccessibilityTest.php`
- `tests/Feature/ResponsiveInterfaceTest.php`
- `tests/Feature/ArchitectureComplianceTest.php`

Expected documentation modifications:

- `docs/accessibility.md`
- `docs/testing.md`
- `docs/plans/forum-current-progress.md`
- `docs/traceability/forum-requirements-matrix.md`
- deterministic evidence files used by the existing requirements overlay

No schema migration is planned. Topic media is already stored as structured
JSON, so new transcript and caption metadata is additive and old rows remain
readable.

## Implementation Plan

### Pass 1: Validation Semantics

Requirement IDs:

- `forum.interface.0010`
- `forum.interface.0011`
- `forum.interface.0012`
- `forum.interface.0013`
- `forum.interface.0014`
- `forum.interface.0016`
- `forum.interface.0017`

Actions:

1. Add one passive Blade error-summary component with a localized heading,
   complete error list, assertive announcement, focus target, and stable data
   hook.
2. Replace duplicated forum error summaries with the component.
3. Add a small navigation-safe JavaScript adapter that focuses a newly
   rendered summary and associates adjacent inline error messages with their
   native form control using `aria-invalid` and `aria-describedby`.
4. Preserve native labels and avoid adding redundant ARIA.

Acceptance:

- The first invalid submission focuses the complete summary.
- Every adjacent inline forum error is programmatically associated with its
  control.
- Repeated Livewire navigation does not duplicate listeners or IDs.
- Error copy remains translated in EN, LT, and RU.

### Pass 2: Media, Tables, Pagination, and Alternatives

Requirement IDs:

- `forum.interface.0015`
- `forum.interface.0018`
- `forum.interface.0019`
- `forum.interface.0020`
- `forum.interface.0025`
- `forum.interface.0026`
- `forum.interface.0027`
- `forum.interface.0028`
- `forum.interface.0029`
- `forum.interface.0032`
- `forum.interface.0033`

Actions:

1. Require a concise media description whenever a topic upload is present.
2. Require a bounded text transcript for video and optionally accept a
   content-validated WebVTT caption file with an explicit supported locale.
3. Store caption files with framework-generated names on the configured
   forum disk and expose only the prepared URL and safe presentation data.
4. Render image alternatives, video captions, and a visible transcript or
   description. Apply a localized compatibility description to legacy media
   without one.
5. Add accessible names to all forum data tables without changing their
   visual structure.
6. Retain native semantic pagination and the existing textual map alternative.
7. Add architecture checks proving no custom dialog or drag-only forum
   interaction is introduced. Do not build unused modal or drag behavior.

Acceptance:

- Missing media description or video transcript fails localized validation.
- Invalid, oversized, or non-WebVTT caption files are rejected.
- Valid captions use generated storage names and render through a `track`.
- Legacy media renders without an empty accessible name.
- Forum tables expose captions or equivalent names and scoped headers.
- Current forum workflows have no drag-only operation.

### Pass 3: Visual and Interaction Resilience

Requirement IDs:

- `forum.interface.0021`
- `forum.interface.0022`
- `forum.interface.0023`
- `forum.interface.0024`
- `forum.interface.0030`
- `forum.interface.0031`

Actions:

1. Verify and, where needed, normalize forum focus, status, pagination, and
   media styles against the existing design tokens.
2. Add automated contrast checks for critical forum text, control, focus, and
   status token pairs.
3. Verify text reflow at 200% zoom-equivalent viewport constraints, translated
   text expansion, mobile touch targets, and no horizontal document overflow.
4. Verify reduced-motion behavior and status communication independent of
   color.

Acceptance:

- Critical token pairs meet WCAG AA contrast.
- Primary workflow controls are at least 44 by 44 CSS pixels.
- No representative page overflows horizontally at mobile or zoom-equivalent
  widths.
- Focus is visible and motion is suppressed when requested.
- Statuses retain readable text or icons rather than color alone.

## Data Migration and Rollback

There is no database migration. Existing topic JSON remains unchanged.
Caption/transcript fields are added only on future media writes. Rollback
consists of reverting application code; additive stored caption files and JSON
keys are harmless to older readers. Failed writes must not persist the topic
or orphan uploaded caption files.

## Legacy Compatibility

- Existing topic URLs, IDs, replies, reactions, subscriptions, bookmarks,
  reports, moderation history, and attachments are untouched.
- Existing media with an empty description receives a localized presentation
  fallback; historical JSON is not rewritten.
- Existing video without captions remains playable and receives the available
  compatibility description.
- Native Laravel pagination and current map behavior remain intact.

## Authorization, Validation, and Privacy

- Topic create/update authorization remains in the existing request and
  policy boundaries.
- Caption and transcript inputs are untrusted, server validated, and never
  rendered as raw HTML.
- Uploaded caption files are content checked and stored under generated names.
- Media descriptions and transcripts must not require private medical,
  address, ownership, or contact details.
- No accessibility change may expose restricted reports, private groups,
  private locations, credential evidence, or authorization-sensitive counts.

## Security and Abuse Risks

- Reject filename-only caption validation, null bytes, malformed WebVTT, and
  files exceeding the bounded size.
- Escape all transcript and media-description output.
- Do not use remote caption URLs or introduce a server-side fetch path.
- Keep summary focus behavior idempotent across `wire:navigate`.
- Architecture checks must reject event-handler HTML and drag-only controls in
  first-party forum templates.

## Cache Impact

No cache key or ownership change is planned. Forum topic presentation cache
behavior, where present, must continue to vary by locale so compatibility
media descriptions cannot cross locale boundaries.

## Tests

Create or update PHP tests for:

- complete and focusable validation summaries;
- native control/error association hooks;
- all forum tables having accessible names and scoped headers;
- semantic headings and pagination landmarks;
- map textual alternatives;
- image description validation and escaped rendering;
- video transcript validation and rendering;
- valid and invalid WebVTT caption uploads;
- generated caption storage names and cleanup on failed persistence;
- EN/LT/RU translation parity for all added copy;
- critical color contrast;
- 44px touch-target and reduced-motion source contracts;
- no custom nonsemantic dialog or drag-only forum operation;
- no empty alt text for meaningful forum topic media;
- direct topic authorization and existing media behavior regressions.

Browser verification must cover public forum index/detail/editor and at least
one class-based Livewire workflow at 375x812 and 1440x900, plus a
zoom-equivalent 320 CSS-pixel content viewport. It must inspect headings,
landmarks, accessible names, focus order, invalid-submit focus, table names,
pagination, media alternatives, reduced-motion styles, target sizes,
horizontal overflow, and console errors.

## Documentation

Update the accessibility guide with implemented behavior, exact automated and
browser evidence, manual limitations, and contributor rules. Update testing,
current progress, deterministic evidence, and requirement status only after
the relevant checks pass.

## Verification Procedure

1. Inspect the complete diff.
2. Run focused accessibility, media, architecture, localization, and affected
   forum tests.
3. Run Pint on modified PHP files.
4. Run Larastan over first-party code.
5. Run the production Vite build.
6. Run browser checks at the required viewports and reduced-motion mode.
7. Run the full serial PHP suite.
8. Run fresh migration, full seed, and repeat seed in an isolated database.
9. Run Composer validation/audit and npm audit.
10. Recalculate deterministic requirement evidence and verify all 24 selected
    IDs have file and passing-test evidence.
11. Inspect the final diff and repository status before commit and push.

## Completion Evidence

This work package is complete only when every selected requirement has:

- production implementation or a technically justified no-new-feature
  applicability decision;
- a passing automated or browser check appropriate to the requirement;
- updated documentation and traceability evidence;
- no known regression in the full serial suite;
- a factual commit and push result.

## Final Package Evidence

Implementation and verification are complete for all 24 selected IDs:

- focused accessibility, responsive, and architecture checks: 28 tests and
  22,936 assertions;
- expanded forum regression: 268 tests and 2,756 assertions;
- localization parity: 7 tests and 27,453 assertions;
- full serial suite: 1,666 tests and 58,350 assertions in 91.946 seconds;
- full Larastan: zero errors; full Pint: passed;
- Composer strict validation/audit and npm audit: zero advisories;
- Vite 8.2.0 production build: passed;
- isolated fresh SQLite: 98 migrations and 172 tables; full seed and repeated
  `DatabaseSeeder`: passed with five users, 1,681 categories, and 13 topics;
- config, event, route, and view cache compilation: passed;
- headless Chrome at 1440x900, 375x812, and 320px completed keyboard,
  Livewire login, invalid submit, admin table, touch target, overflow, and
  console checks with no error.

No migration or destructive data rewrite was introduced. Existing topics and
all related answers, replies, reactions, subscriptions, bookmarks, reports,
moderation history, and attachments remain under their original identities.
