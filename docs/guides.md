# Collaborative Guides

This document is the canonical operating contract for community-maintained
guides. Guides are `KnowledgeArticle` records and remain separate from ordinary
forum topics. A resolved topic can be converted only by an authorized explicit
action; votes, reactions, and popularity never publish or create a guide.

## Workflow

```text
draft -> submitted-for-review -> changes-requested -> submitted-for-review
                              \-> community-reviewed -> published
                                                     \-> expert-reviewed -> published

published -> correction-requested -> submitted-for-review
         \-> outdated -> submitted-for-review / archived / replaced
         \-> archived
         \-> replaced
```

Community review requires an independent active user with scoped forum trust.
Expert review requires an independent active user with a current verified
expert profile. Administrator status does not impersonate either review.
Publishing remains a separate maintainer or administrator decision.

Every state transition appends a `knowledge_workflow_events` row. Every content
save appends a `knowledge_versions` snapshot. Both are append-only Eloquent
records. Rollback copies an earlier snapshot into a new current version and
records a rollback event; it never rewrites history.

## Scope And Attribution

A guide can record:

- one stable translation group and one locale within that group;
- optional jurisdiction and global taxon scope;
- optional source and discussion topics;
- cited HTTP or HTTPS sources;
- protected editorial sections;
- normalized maintainers, contributors, community reviewers, expert reviewers,
  and translators;
- a replacement guide when the current guide is superseded.

Public pages expose only published or outdated locale variants. Draft locale
variants, editorial lock reasons, private correction decisions, unpublished
versions, and workflow metadata are not public.

## Translation Workflow

An active verified user with guide-create trust can start a translation only
when the source guide is visible to that user. A private or draft source also
requires update authority. The named translation route opens the class-based
`KnowledgeGuideEditor` with a locked source ID; every render and save reloads
and reauthorizes the source.

Translation creation:

1. validates the target against supported locales;
2. rejects the source locale and an existing locale in the same family;
3. locks the source row inside a short transaction;
4. creates a separate draft with `human-community` provenance;
5. records the source article and translator;
6. creates the maintainer relation, first immutable version, and workflow
   event;
7. redirects to the normal versioned editor.

Title, summary, and body start empty. Safe category, type, difficulty,
jurisdiction, taxon, discussion, and cited-source scope may be copied, but
source prose and protected sections are never silently copied. The database
unique constraint on translation family and locale remains the final
concurrency boundary.

Public translated guides show the translation source, translator, and source
guide only when the source itself is public to the viewer. A public
translation cannot leak a private source title. Corrections, review,
publication, rollback, and replacement use the same independent workflow as
every other guide.

## Editing

The class-based `KnowledgeGuideEditor` Livewire component uses a separate form
object. Browser-controlled identifiers are locked and every mutation reloads
the guide, authorizes the current actor, and validates submitted values.
`lock_version` implements optimistic concurrency: a stale editor must reload
instead of silently replacing another editor's work.

An editorial lock is an explicit audited coordination tool. It does not replace
authorization. Only the locking maintainer or an administrator can save while
the lock is active. At least one active maintainer must remain.

## Corrections And Discussion

Members propose corrections against a recorded base version. An authorized
maintainer accepts, rejects, or applies the proposal with a reason. Accepting a
correction against published guidance moves the guide to
`correction-requested`; applying content still requires a normal versioned edit.

Discussion links point to existing forum topics. The guide body remains escaped
plain text at the presentation boundary. Source URLs are rendered as external
links and are never fetched by the application.

## Export And Print

Published and outdated guides have bounded UTF-8 Markdown export and a semantic
print layout. Draft export and print require update authority. Responses set
`nosniff`; exports do not include editorial locks, private correction evidence,
or workflow metadata.

## Demo Data

`CollaborativeGuideDemoSeeder` runs only inside the already environment-gated
`DatabaseSeeder`. It upgrades two stable demo guides with deterministic
translation groups, maintainers, discussion links, immutable workflow events,
and version snapshots. Repeated execution preserves IDs and stable counts.

## Recovery And Rollback

For an incorrect edit, use version rollback so the correction remains
auditable. For an incorrect state, apply a valid forward transition with an
explanation. Do not update or delete version or workflow rows.

The schema migration is additive. Application rollback should retain its
columns and history tables after collaborative data exists. A schema `down()`
is suitable only before production use; operators must export guide versions
and workflow events plus translation provenance before any exceptional
destructive rollback.

## Verification

Primary behavioural evidence:

- `tests/Feature/Forum/CollaborativeGuideWorkflowTest.php`;
- `tests/Feature/ForumTopicTest.php`;
- `tests/Feature/KnowledgeBaseTest.php`;
- `tests/Feature/Database/FactoryAndSeederTest.php`;
- `tests/Feature/ArchitectureComplianceTest.php`;
- `tests/Feature/LocalizationTest.php`.
- `tests/Feature/Forum/ForumMultilingualBehaviorTest.php`.
