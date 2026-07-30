# Forum and Knowledge Base Scope

## Delivered in the first stable release

- Public, registered-only, group, specialist, link-only, and draft visibility modes.
- Questions and discussions with categories, tags, pet context, media, drafts, and similar-topic suggestions.
- Search, category and status filters, unanswered and specialist views, deterministic sorting, and pagination.
- Answers, comments, reactions, useful votes, accepted answers, solved states, author updates, bookmarks, and subscriptions.
- Reports, blocks, moderation priority, medical warnings, dangerous-advice reporting, and private-topic cache protection.
- Editorial knowledge articles with sources, contributors, review dates, versions, related discussions, and correction proposals.
- Responsive Blade interfaces for the directory, topic, editor, knowledge library, and article pages.
- Factories, representative seed data, policies, Form Requests, Actions, thin controllers, and focused feature tests.

## Safety invariants

- Forum content never presents itself as emergency veterinary care.
- Private and draft topics are excluded from public directories, search suggestions, and knowledge conversion.
- Medical files or pet records are never attached automatically.
- A useful or accepted answer is not presented as a verified medical conclusion.
- Reports and notifications use idempotent actions so retries do not create duplicates.
- Blade templates receive prepared view data and do not query Eloquent.

## Deliberately deferred

The later-release items from section 305 remain outside this release: anonymous topics, professional closed areas, semantic or AI search, collaborative answers, audio and video answers, automatic transcripts, live expert sessions, advanced reputation, editorial teams, local article variants, offline packs, answer comparison, automated stale-content detection, advanced anti-spam analytics, and AI summaries.

The schema and service boundaries keep these additions possible without changing the public topic, answer, comment, vote, subscription, report, or knowledge-article contracts.
