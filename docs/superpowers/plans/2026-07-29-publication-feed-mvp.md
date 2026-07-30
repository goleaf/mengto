# PawCircle Publication Feed MVP Implementation Plan

> Historical delivery record. Production requirements, security rules, testing gates, and runtime versions are governed by `docs/index.md` and its canonical documents.

## Goal

Deliver the Point 2 feed as a functional, responsive, session-backed Blade MVP
without creating or modifying PHP test files.

## Phase 1: Contracts

1. Add a dedicated `BrowseFeedRequest`.
2. Validate feed mode, ordering, content type, pet interest, and page.
3. Extend composer browsing with post and identity context.
4. Keep `/` canonical and preserve filter query strings in generated links.
5. Add composer kinds for editing and reporting a post.

## Phase 2: Mutable Prototype State

1. Add normalized post records independent from groups, meetups, and pets.
2. Add create, update, archive, restore, delete, and lookup operations.
3. Add draft and published state.
4. Add one reaction per post and supported reaction values.
5. Add stable IDs and optional parent IDs to comments.
6. Add post reports.
7. Reuse toggle collections for save, subscribe, hide, and mute.
8. Cap every session collection to avoid unbounded payload growth.

## Phase 3: Feed Data Services

1. Create `FeedCatalog` for immutable identities, stories, media, and
   seeded posts.
2. Create `FeedPresenter` for filtering, ordering, pagination, and
   interaction decoration.
3. Move home-feed post data out of `PreviewService`.
4. Keep existing directory, profile, and conversation contracts compatible.
5. Resolve post detail, edit, report, share, and back-to-feed contexts centrally.

## Phase 4: Action Layer

1. Expand `PerformActionRequest` action and payload unions.
2. Add publish and save-draft actions.
3. Add update, archive, restore, and delete actions.
4. Add reaction selection.
5. Add repost, subscribe, hide, mute, and report actions.
6. Extend comment creation with optional one-level parent context.
7. Return named routes with filter/anchor-compatible parameters.

## Phase 5: Composer

1. Upgrade the post definition to identity-aware fields.
2. Add post type, topic, audience, comment policy, safe place, tags, media
   preset, alt text, and sensitive-content fields.
3. Provide separate Publish and Save draft commands.
4. Prefill the same component for editing.
5. Add a private report composer.
6. Show a concise media/audience summary before the actions.

## Phase 6: Shared Components

1. Build feed mode and filter navigation from generic tab/filter controls.
2. Build a compact story rail.
3. Split post identity, context, badges, body, media, meta, and overflow actions.
4. Add accessible multi-image media strip.
5. Add a native video component.
6. Add reaction selector and aggregate summary.
7. Add lifecycle and safety action groups.
8. Extend comments with one-level reply context.
9. Keep page templates declarative and short.

## Phase 7: Page Composition

1. Replace the current `feed-stream` heading with a complete feed dashboard.
2. Add quick composer, mode rail, sort control, content/pet filters, and stories.
3. Add finite pagination and a clear end state.
4. Add drafts, saved, and archive views through the same stream.
5. Keep desktop center column readable and mobile media dimensions stable.

## Phase 8: SCSS

1. Extend `_feed.scss` with feed toolbar, stories, identity, context, media
   strip, reaction rail, menu, and pagination styles.
2. Extend `_forms.scss` only for composer-specific summaries/actions.
3. Reuse design tokens and existing control mixins.
4. Keep card radius at the current system value.
5. Verify no viewport-wide overflow at 320 px.

## Phase 9: Documentation

1. Update `PRODUCT.md` with separate publishing identities and MVP feed modes.
2. Update `DESIGN.md` with catalog/presenter/state boundaries.
3. Record deferred database, upload, moderation, and recommendation work.

## Phase 10: Verification

1. Run PHP syntax checks and targeted Pint.
2. Cache Blade templates.
3. Build Vite assets.
4. Audit 4-layer Blade component dependencies.
5. Confirm zero runtime `x-pet-social` references.
6. Confirm no query or business logic in Blade.
7. Run Playwright route matrix across five widths.
8. Run all reversible feed workflows.
9. Inspect representative screenshots and loaded media.
10. Run existing PHP tests diagnostically and report stale baseline failures
    without editing test files.
11. Run staged/unstaged diff checks and confirm `tests/` is untouched.
