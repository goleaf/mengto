# Phase 8 Collaborative Guides Work Package

Last updated: 2026-07-31.

## Requirement Scope

This package implements the complete collaborative-guides source section:

- `forum.feature.2845` through `forum.feature.2874`;
- `forum.translation.0012`;
- `forum.interface.0038`.

No adjacent search, feed, notification, badge-grant, or generic rich-text
requirement is marked complete by this package.

## Current Implementation Analysis

The existing `KnowledgeArticle` domain is the canonical guide boundary and must
be extended rather than replaced. It already provides:

- separate articles, versions, and correction proposals;
- public library and article routes;
- policy-protected correction submission;
- source-topic links, citations, review dates, locale, and contribution
  attribution;
- published/outdated visibility and bounded paginated browsing;
- three model factories and focused feature tests.

The current implementation does not yet provide the complete required workflow,
normalized maintainers and contributors, scoped editorial permissions,
optimistic editing, audited transitions, locale-family links, jurisdiction and
taxon scope, protected sections, editorial locks, rollback, or dedicated export
and print representations. Existing `review` rows require a compatible backfill
to the explicit `submitted-for-review` state.

## Desired Result

`KnowledgeArticle` remains the authoritative guide record. Community guides are
separate from ordinary forum posts and move through an explicit, audited
workflow:

1. draft;
2. submitted for review;
3. changes requested;
4. community reviewed;
5. expert reviewed where relevant;
6. published;
7. correction requested;
8. outdated;
9. archived;
10. replaced.

Every content change creates an immutable version. Rollback creates a new
version instead of deleting history. Maintainers and contributors are
normalized relations to active users. Community review requires scoped trust;
expert review requires a current independently verified professional identity.
Publishing remains an explicit authorized decision and cannot be inferred from
forum popularity.

## Affected Files

Expected modifications:

- `app/Enums/KnowledgeStatus.php`;
- `app/Models/KnowledgeArticle.php`;
- `app/Models/KnowledgeVersion.php`;
- `app/Models/KnowledgeCorrection.php`;
- `app/Models/User.php`;
- `app/Policies/KnowledgeArticlePolicy.php`;
- `app/Services/KnowledgePresenter.php`;
- knowledge controllers, requests, routes, views, translations, factories,
  seeders, and tests;
- canonical architecture, data, security, operations, seeding, testing,
  progress, traceability, and changelog documents.

Expected additions:

- one additive guide-workflow migration;
- collaborator and workflow-event models and factories;
- knowledge collaborator-role and correction-status enums;
- actions for saving, transitions, correction review, collaborator management,
  and rollback;
- one class-based Livewire guide editor with a separate form object and Blade
  view;
- print and export controllers/views;
- a focused collaborative-guide feature test.

## Schema Changes

The additive migration will:

- preserve every `knowledge_articles.id`, slug, source-topic relation, locale,
  current version, version row, and correction row;
- backfill legacy `review` to `submitted-for-review`;
- add translation-family, jurisdiction, taxon, discussion-topic, replacement,
  optimistic-lock, protected-section, and editorial-lock fields;
- extend version snapshots with all fields required for faithful rollback;
- extend correction proposals with user identity, base-version, review
  decision, and audit timestamps;
- add normalized collaborator and append-only workflow-event tables;
- add foreign keys, uniqueness, and leading indexes for actual workflow
  queries.

No existing table is truncated or rebuilt. JSON contributor attribution remains
readable during the compatibility period and is presented alongside normalized
collaborators.

## Data Migration Strategy

The migration uses schema builder and bounded Eloquent/query-builder updates.
Existing articles receive a deterministic translation-family key derived from
their stable article identity. Existing `review` status becomes
`submitted-for-review`. Existing JSON contributors remain unchanged; no user
identity is guessed from display names.

## Rollback Strategy

Production rollback is application-first: deploy the previous application while
retaining the additive columns and tables. Schema down-migration is safe only
before new collaborative data is accepted. The test-only `down()` maps expanded
review states to the closest legacy state before dropping additive structures.
Operators must export workflow events and versions before any exceptional
post-usage schema rollback.

## Legacy Compatibility

- Existing article slugs and public URLs remain stable.
- Published and outdated visibility remains unchanged.
- Existing correction forms remain supported.
- Existing JSON citations and contributor labels remain renderable.
- Existing forum-to-guide conversion remains explicit, owner-triggered, and
  creates a review draft; popularity never converts content automatically.

## Authorization

- Public users may view only published or outdated guides.
- Active members may propose corrections to visible guides.
- Active trusted contributors may create drafts.
- Maintainers and administrators may edit and manage collaborators.
- Scoped community reviewers may record community review.
- Only a user with current independently verified professional status may
  record expert review; administrators may not impersonate that review.
- Publishing, archival, replacement, editorial locking, correction decisions,
  and rollback require maintainer or administrator authority.
- Every Livewire mutation repeats authorization server-side.

## Validation

- Titles, summaries, bodies, locales, jurisdictions, source URLs, taxon IDs,
  role values, transition targets, correction decisions, edit reasons, lock
  versions, and replacement targets are server validated.
- Source URLs allow only HTTP and HTTPS.
- The target taxon and discussion topic must exist.
- Translation locale pairs are unique within a translation family.
- Replacement cannot point to the same guide.
- Optimistic lock mismatch returns a localized conflict error.

## Translation

All workflow states, roles, labels, validation messages, feedback, empty states,
lock notices, export labels, print labels, and accessibility names are added to
the existing `knowledge.php` catalogues for `en`, `lt`, and `ru`. User-authored
guide content remains in its recorded locale and scientific names are not
translated.

## Interface And Accessibility

The editor is a normal class-based Livewire 4 component with a separate form
object and Blade template. It exposes explicit save, submit, review, publish,
rollback, collaborator, and correction controls according to policy. Controls
have labels, associated errors, action-specific loading state, stable layout,
keyboard operation, visible focus, and at least 44-pixel touch targets. Print
output uses semantic headings and source links.

## Cache

The public library currently uses bounded database queries and no article
content cache. This package does not introduce speculative caching. Future
guide cache entries must be invalidated on save, transition, correction
decision, rollback, locale update, or replacement.

## Security, Privacy, And Abuse Risks

- Drafts, private editorial notes, lock reasons, reviewer decisions, and
  unpublished versions must not leak through public routes or search.
- Expert review must derive from independent credential state, not karma.
- Optimistic locking prevents silent concurrent overwrite.
- Correction URLs are displayed only as links and are never fetched by the
  server.
- Article content remains escaped plain text at the presentation boundary.
- Collaborator grants and workflow transitions are audited.
- Popularity and post volume have no publishing path.

## Tests

Create or update tests for:

- legacy status/data preservation and schema indexes;
- complete valid and invalid transition graph;
- public visibility for every workflow state;
- creator, contributor, maintainer, community-reviewer, expert-reviewer, and
  administrator policy boundaries;
- direct Livewire mutation authorization and validation;
- optimistic edit conflict;
- immutable version creation and rollback-as-new-version;
- correction proposal and review;
- collaborator uniqueness and revocation;
- locale-family uniqueness, jurisdiction, taxon, source-topic, and discussion
  links;
- export and print privacy;
- factories and meaningful states;
- translation parity and architecture constraints;
- explicit prevention of popularity-driven guide conversion.

## Documentation Updates

Update:

- `docs/architecture.md`;
- `docs/domain-model.md`;
- `docs/data-model.md`;
- `docs/authorization.md`;
- `docs/livewire.md`;
- `docs/security.md`;
- `docs/operations.md`;
- `docs/seeding.md`;
- `docs/testing.md`;
- forum ADRs, progress, gap analysis, compliance evidence, and `CHANGELOG.md`.

## Acceptance Criteria

- All scoped workflow states exist and are transition-tested.
- Existing articles, versions, corrections, IDs, slugs, and public behavior are
  preserved.
- Normalized maintainers/contributors and scoped review authority work.
- Every edit and transition has immutable evidence.
- Concurrent edits cannot silently overwrite.
- Rollback appends a version and preserves the reverted version.
- Locale, jurisdiction, species/taxon, cited sources, discussion, protected
  sections, attribution, export, and print are represented.
- No popular post becomes a guide automatically.
- All new user-facing strings have EN/LT/RU parity.
- Targeted tests, Pint, Larastan, architecture checks, production build, and
  fresh migration/seed verification pass before evidence becomes verified.

## Verification Procedure

1. Run focused knowledge/workflow/policy/schema tests.
2. Run factory, seeder, localization, and architecture gates.
3. Run Pint on modified PHP and then the full formatting check.
4. Run Larastan at the repository-configured level.
5. Run the complete serial Pest suite.
6. Run the isolated fresh migration and repeat-seed verifier.
7. Run the production Vite build.
8. Verify public article, editor, print, and export flows at mobile and desktop
   widths with no console errors or main-content overflow.
9. Regenerate deterministic requirement and seeding evidence.
10. Update each scoped requirement with exact file and command evidence.

## Completion Evidence

- Additive schema and leading indexes:
  `2026_07_31_000900_add_collaborative_guide_workflow.php` and
  `2026_07_31_000910_add_collaborative_guide_foreign_key_indexes.php`.
- Domain behavior: seven transactional Actions, direct policy boundaries,
  immutable versions/events, optimistic locking, correction review,
  collaborator management, rollback, and editorial locks.
- Interface: class-based `KnowledgeGuideEditor`, separate Blade view, bounded
  taxonomy selector, public locale variants, print/export, and administration
  registry.
- Focused schema/workflow verification:
  `php artisan test tests/Feature/Database/SchemaIntegrityTest.php
  tests/Feature/Forum/CollaborativeGuideWorkflowTest.php --compact` passed
  18 tests and 111 assertions.
- Complete serial verification: `php artisan test --compact` passed 1,050 tests
  and 43,663 assertions.
- `php scripts/verify-fresh-database.php` passed 89 migrations, 124 tables,
  fresh seed, and repeat seed with the user count stable at 5.
- `vendor/bin/phpstan analyse --no-progress --error-format=json` reported zero
  errors; `npm run build` passed with Vite 8.2.0.
- Playwright verified the public guide, editor, print view, and admin registry
  at 375x812 and 1440x900 without horizontal overflow, raw keys, unnamed
  buttons, undersized primary controls, or current-page console errors.
- `forum.feature.2845` through `forum.feature.2874`,
  `forum.translation.0012`, and `forum.interface.0038` are recorded as
  verified in the deterministic evidence overlay.
