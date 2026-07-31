# Community Review And Notes

This document is the canonical operational and architecture contract for
low-risk community review panels and contextual community notes. It implements
`forum.feature.2655` through `forum.feature.2684`,
`forum.moderation.0029`, `forum.moderation.0030`,
`forum.category.1387`, `forum.translation.0009`,
`forum.plan.0072`, `forum.feature.2704` through
`forum.feature.2728`, and `forum.translation.0010`.

## Boundary

Community review is an assigned, independent classification workflow. It is
not a popularity counter and cannot make final decisions about threats, child
safety, private personal data, serious harassment, cruelty evidence, illegal
trade, credential fraud, severe medical misinformation, legal demands,
private payment disputes, or permanent bans. Those subjects remain in the
unified human moderation system.

Supported low-risk panel types are duplicate-topic, wrong-category, tag,
translation, guide-clarity, identification-confidence, and non-sensitive
content-quality review. The enum is the server-side allowlist.

## Data Model

- `ForumReviewPanel` stores the controlled subject, type, low-risk
  classification, requester, quorum, deadline, state, decision, appeal, and
  bounded public context.
- `ForumReviewAssignment` stores an independently selected reviewer,
  anonymized reference, reasoning, conflict declaration, decision, deadline,
  and replacement lineage. A database unique key enforces one reviewer per
  panel.
- `ForumReviewPanelEvent` is append-only. It records creation, review,
  recusal, replacement, cancellation, expiry, community decision, moderator
  review, override, and appeal.
- `ForumCommunityNote` is the current projection for a contextual note on a
  topic or answer.
- `ForumCommunityNoteVersion` is append-only and records every proposal,
  review start, author response, assessment, revision, moderation outcome, and
  revalidation.

No private report evidence is copied into a panel. Reviewers receive only the
public title/excerpt and an anonymized reviewer reference.

## Eligibility And Assignment

A proposer must be an active administrator or hold a current eligible trust
assignment. A reviewer must be active, email-verified, and hold a current
community-reviewer, category-steward, moderator, or senior-moderator level.

Selection excludes the requester, content author, declared conflicts, and
every reviewer already assigned to the panel. Eligible users are ordered by
active assignment count, account age, and stable user ID. This provides
deterministic load balancing without exposing reviewer identity publicly.

Reviewers submit one reasoned decision before the stored deadline. Recusal
requires a conflict description and triggers an audited replacement attempt.
Expiry is enforced at write time and does not depend on cron. Moderator
replacement and overrides require administrator authorization and a reason.

## Community Note Workflow

The eleven controlled note purposes cover outdated information, missing
context, jurisdiction and species differences, safety warnings, source and
translation corrections, conflicts of interest, sponsored disclosures,
product recalls, and duplicate-case context.

```text
proposed -> in-review -> community-assessed -> moderator-review
        \-> gathering-evidence -> in-review
moderator-review/community-assessed -> published / rejected / archived
published -> revised / revalidation-due
```

Every note requires one to eight HTTP or HTTPS evidence references, a bounded
content-focused explanation, and an eligible proposer. Per-user/subject rate
limits and an open-note cap reduce harassment and flooding.

If a note changes while a panel is open, the old panel and its pending
assignments are cancelled with immutable history. The revised note returns to
gathering evidence and must receive a new independent review. Published notes
may be revised only by an administrator; revision records a new version and a
new 90-day safety or 180-day normal revalidation deadline.

The content author may append a response but cannot edit, hide, or delete an
approved safety note. Moderator publication, rejection, archive, revalidation,
and override always create new evidence records. Eligible requesters and
subject authors may appeal a final panel decision.

## Interface And Localization

`CommunityNotesPanel` is a class-based multi-file Livewire component mounted
on the forum topic page. Its topic identity is locked. Every action parameter
is treated as untrusted, reloaded server-side, and authorized again inside the
domain action.

Guests see only published, revised, or revalidation-due notes. Proposers,
subject authors, assigned reviewers, and administrators receive only their
authorized pending projections. Queries are bounded and the panel relation is
eager loaded.

The interface provides semantic headings, escaped user content, source links
with safe external-link attributes, field-associated errors, precise loading
targets, an offline state, visible status labels, and keyboard-operable forms.
All platform text exists in EN, LT, and RU under `forum_review`.

## Operations And Recovery

Do not edit event or version rows. Recover an incorrect outcome with an
audited forward operation:

1. appeal or record a moderator outcome with a reason;
2. revise the note, which appends a version;
3. restart independent review when a pending note changed;
4. replace a conflicted or unavailable reviewer;
5. allow an expired panel to fail closed and create a new panel if review is
   still needed.

The migration is additive and does not alter legacy topics, answers, votes,
reports, or moderation cases. After community data exists, application
rollback should retain these tables. Database rollback is appropriate only
before the feature accepts production data.

## Verification

- `tests/Feature/Forum/CommunityReviewAndNotesTest.php`
- `tests/Feature/Database/SchemaIntegrityTest.php`
- `tests/Feature/Database/FactoryAndSeederTest.php`
- `tests/Feature/LocalizationTest.php`
- `tests/Feature/ArchitectureComplianceTest.php`

The focused suite covers all panel and note types, prohibited high-risk panel
subjects, eligibility, balancing, conflict replacement, expiry, quorum,
versioning, optimistic edits, stale-panel cancellation, moderator outcomes,
appeals, privacy, database uniqueness, and direct Livewire authorization.
