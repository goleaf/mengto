# Forum Journals

## Purpose And Boundary

Forum journals are public or selectively shared progress records built on the
forum discussion shell. They do not replace `CareJournal`, which remains the
private operational care domain for routines, medication-adjacent data,
encrypted measurements, temporary grants, and private care media.

Every `ForumJournal` owns exactly one `ForumTopic`. The topic remains
authoritative for author, category, group, locale, visibility, URL,
engagement, reports, subscriptions, bookmarks, moderation, and animal
context. The journal owns its type, archive state, entries, normalized
measurements, collaborators, entry versions, and private media.

## Supported Types

The typed catalogue contains a neutral `general` fallback and the eleven
required journal types:

- training;
- behavior;
- recovery;
- weight;
- rehabilitation;
- adoption adaptation;
- foster;
- aquarium;
- terrarium;
- pregnancy and newborn;
- senior care.

The fallback is used only when an explicitly typed legacy journal topic has no
valid structured subtype. It is marked for review; title or body keywords are
never used to infer sensitive meaning.

## Lifecycle

1. An authenticated active user creates a journal through
   `CreateForumJournal`.
2. The Action validates and normalizes the request, creates the topic and
   journal transactionally, and records an audit event.
3. Owners and active editor collaborators may add entries, milestones, and
   setbacks with an occurrence date and type-specific measurements.
4. Entry edits use optimistic locking and preserve the prior state in
   `forum_journal_entry_versions`.
5. Authorized viewers may comment through the existing `ForumComment`
   boundary.
6. Owners grant or revoke viewer/editor collaboration. Revocation preserves
   historical attribution and immediately removes future access.
7. Owners or editors may export a bounded JSON record. Owners may archive the
   journal without deleting its topic, history, files, comments, or audit
   evidence.

Archived journals remain readable to currently authorized viewers but reject
new mutations.

## Privacy And Authorization

Visibility has one source: `forum_topics.visibility`.

- public journals are discoverable to guests;
- member journals require an active authenticated account;
- group journals require current group access;
- expert journals require current independently verified professional
  evidence;
- link-only journals require an authorized direct path and are omitted from
  public discovery;
- private journals require ownership or an active selected collaboration.

Policies run before entries, counts, charts, media, and exports are queried or
serialized. `#[Locked]` component IDs reduce accidental mutation but never
replace policy authorization. Private pet records, exact addresses,
credentials, care records, storage paths, idempotency keys, and private
original filenames are excluded from browser state and exports.

## Measurements And Progress

`ForumJournalMetricRegistry` owns the allowed metric keys, units, ranges, and
applicable journal types. Unknown keys, duplicate keys, invalid units, and
out-of-range values are rejected before persistence.

Progress presentation is bounded and ordered. It uses semantic tables,
textual values, and native progress elements where a percentage-like value is
meaningful. The interface does not calculate streaks, punish missed updates,
rank users, shame setbacks, or create reputation/professional authority.

## Media

Journal images are stored on the configured private local disk with generated
paths. Uploads require actual image content, bounded size and dimensions,
alt text, an idempotency key, and request-time authorization. The original
name is encrypted at rest. Downloads are served only through
`ForumJournalMediaController`; there is no public storage URL.

If persistence fails after writing a file, the Action removes the partial
file. Parent journal and entry identity are checked explicitly even when
route binding succeeds.

## Backfill And Seeding

`BackfillForumJournals` and `ForumJournalBackfillSeeder` process only topics
whose stored topic type is exactly `journal`. They preserve topic IDs, URLs,
answers, comments, reactions, subscriptions, bookmarks, reports, media, group
relations, and moderation history. Stable one-to-one and idempotency
constraints make reruns safe.

`ForumJournalDemoSeeder` is limited to local, demo, and testing environments.
It uses production Actions and deterministic keys. It does not create demo
identities or content in production.

## Recovery

- A conflicting entry edit is retried after reloading the latest
  `lock_version`; no version row is deleted.
- A wrongly archived journal is restored only through a reviewed forward
  Action or migration that records audit evidence.
- A wrong legacy subtype is corrected through an authorized typed update; the
  topic and journal IDs remain stable.
- Missing private media is treated as an incident. Do not expose another disk
  or path as a fallback.
- After user data exists, rollback is forward-only. Do not drop journal tables
  or the journal-comment relation.

## Verification

Primary coverage lives in
`tests/Feature/Forum/ForumJournalWorkflowTest.php`. It covers schema,
localization, creation, legacy backfill, visibility, entries, measurements,
optimistic history, collaborators, comments, private files, export, routes,
archive, Livewire direct actions, and repeated seed behavior.

The requirement scope and exact executed checks are recorded in
`docs/plans/forum-phase8-journals-work-package.md`.
