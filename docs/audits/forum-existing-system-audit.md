# Forum Existing System Audit

## Audit Scope

This audit records the system as it existed at `main`
`260bc135ed58cc95d2b8ed875b547fe816adb9ee` before forum modernization.
The only pre-existing untracked path was `.agents/vendor/`; it is unrelated
user-owned work and must not be staged or changed.

## Baseline

| Check | Observed result |
| --- | --- |
| PHP | 8.5.8 |
| Laravel | 13.23.0 |
| Livewire | 4.3.3 |
| Tailwind | 4.3.3 |
| Vite | 8.2.0 |
| Test framework | Pest 4.7.5 |
| Application routes | 147 |
| First-party Eloquent models | 66 |
| Factories | 67 |
| PHP test files | 34 |
| Targeted forum baseline | 11 tests, 68 assertions, passed |
| First-party Blade `@php` / `@endphp` | none |
| Forbidden raw-query patterns in app/views/routes | none in the audited search |

Baseline forum command:

```bash
php artisan test tests/Feature/ForumDirectoryTest.php \
  tests/Feature/ForumTopicTest.php \
  tests/Feature/KnowledgeBaseTest.php --compact
```

## Existing Forum Request Flow

```text
named middleware-grouped routes
  -> Form Request
  -> thin controller
  -> policy or action-level ownership check
  -> Eloquent action/presenter
  -> Blade, redirect, or explicit JSON
```

The public forum currently supports browsing, filtering, similar-topic JSON,
topic detail, and knowledge articles. Authenticated active users can create,
edit, answer, comment, bookmark, subscribe, vote, accept one answer, resolve,
report, block an author, and convert a resolved topic into a knowledge draft.

## Existing Persistence

The forum uses `forum_topics`, `forum_answers`, `forum_comments`,
`forum_votes`, `forum_engagements`, `forum_blocks`, `forum_reports`, and
`forum_notifications`. Knowledge content uses `knowledge_articles`,
`knowledge_versions`, and `knowledge_corrections`.

Important compatibility facts:

- topic category and subcategory are string columns;
- authors are identified by stable actor keys;
- one answer can be accepted through `accepted_answer_id`;
- votes are unique per answer and actor key;
- reports contain optional topic, answer, or comment foreign keys;
- current forum records do not use soft deletion;
- existing URLs bind topics and articles by slug.

## Existing Adjacent Domains

PawCircle already has normalized domains that must be reused:

- pet profiles;
- expert profiles and private credentials;
- marketplace listings, reservations, orders, disputes, and reviews;
- lost/found search cases, sightings, volunteers, tasks, alerts, and reports;
- medical, care, device, social, places, and audit records.

The new forum must link to these records rather than create incompatible
copies. Lost/found and marketplace workflows remain authoritative for their
structured operations; forum topics can discuss or reference them.

## Existing Authorization

`ForumTopicPolicy` protects visibility, ownership, answers, comments, and
deletion. `ForumAnswerPolicy` protects answer ownership. Knowledge publication
is administrator-only. Gaps include no explicit forum moderator capability,
no report/case/appeal policies, no taxonomy-curator ability, and no dedicated
authorization for reputation or confirmation.

## Existing Validation And Files

Substantial writes use Form Requests. Forum image and video uploads validate
declared content types and size and use generated storage paths. The current
topic taxonomy is injected into validation, but the taxonomy itself is an
in-memory English catalogue. New structured fields, moderation evidence, and
private credential/report attachments require dedicated validation and
private disks.

## Existing Presentation

The forum is server-rendered Blade with progressive JavaScript. There are no
forum Livewire components. Blade receives prepared arrays from presenters and
contains no database access. Platform text is translated through Laravel
catalogues for `en`, `lt`, and `ru`, but option labels in `ForumTaxonomy` are
partly hardcoded English and must move into the established catalogues.

## Existing Search And Cache

Search uses bounded Eloquent queries and database text matching. No external
search server is installed. Cache is database-backed locally and configurable
through Laravel. The existing forum taxonomy is not cached because it is an
in-process array. The modernization may add deterministic cache keys for
category trees and taxonomy roots but may not make core browsing depend on a
new search or cache service.

## Deployment Constraints

SQLite is mandatory for local and automated tests. The project supports
database queues and scheduler configuration, but critical user-visible
functionality must retain an ordinary request or resumable web/command path.
The taxonomy importer therefore works against a local snapshot in bounded,
restartable chunks and does not require a permanent external API connection.

