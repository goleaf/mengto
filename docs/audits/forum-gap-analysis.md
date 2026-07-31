# Forum Gap Analysis

## Summary

The existing forum is a tested vertical slice, not the complete community
platform defined by the preserved specification. Modernization must be
additive and retain the working topic, answer, knowledge, lost/found, expert,
and marketplace behaviour while introducing normalized shared domains.

## Critical Gaps

| Area | Existing state | Required state | Resolution |
| --- | --- | --- | --- |
| Requirements | Focused `docs/forum-scope.md` | Immutable combined source plus 7,284 atomic records | Preserved and generated in Gate 0 |
| Categories | 11 PHP-array categories; string topic fields | 44 stable top-level categories and complete seeded children | Normalize categories; retain legacy strings during expand phase |
| Taxonomy | Pet species/breed strings | Versioned global scientific and domestic taxonomy | Separate reusable taxonomy module and local-snapshot importer |
| Reputation | Answer helpful count only | Append-only scoped ledger, aggregates, reversals, trust, badges | Dedicated models/actions and database uniqueness |
| Confirmation | No structured consensus | Eligibility, quorum, evidence, expiry, review and audit | Dedicated confirmation domain |
| Moderation | Narrow topic/answer reports | Unified reports, cases, actions, appeals, recusal, private evidence | Expand report model and add case domain |
| Accepted answers | One accepted answer | Multiple accepted answers, history, invalidation, solved-state recalculation | Add acceptance records; retain legacy pointer |
| Structured content | A few topic fields | Versioned topic schemas and type-specific validation | Topic types plus validated JSON and dedicated high-value relations |
| Interface | Conventional forms | Class-based Livewire for taxonomy, reporting, and administration | Add focused components without converting static presentation |
| Localization | Core text translated; taxonomy labels partly English in PHP | All system-controlled labels in `en`, `lt`, and `ru` | Stable catalogue keys and parity tests |
| Seeds | Demo forum only; early return when any topic exists | Production-safe idempotent definitions plus separate demo graph | Split fixed and demo seeders |
| Tests | 11 focused forum tests | Requirement-linked policy, seed, race, privacy, import and UI tests | Add tests in every implementation pass |

## Security Findings

### Resolved And Verified

- Self-voting and duplicate voting are rejected at the action and database
  boundaries; reputation effects use a separate append-only ledger.
- Unified reports provide role-scoped private evidence, immutable events,
  moderation cases, recusal, actions, and independent appeals.
- Topic and search-case animal context now resolves from owned pet profiles or
  the global taxonomy instead of a hardcoded browser-owned list.
- Professional public state is projected from independently reviewed,
  purpose-compatible credentials with expiry, suspension, revocation, and
  appeal history.
- Lost-and-found exact locations, contact details, and sensitive animal notes
  remain encrypted and are never included in public projections or contact
  relays.
- Collaborative guides now use explicit review states, normalized scoped
  collaborators, immutable versions/events, optimistic edit locks, correction
  review, protected sections, locale/taxon/jurisdiction scope, and controlled
  print/export boundaries. Popularity has no automatic publication path.
- Peer mentorship now uses explicit opt-in, bounded scoped matching,
  participant-only append-only communication, block/report checks, independent
  professional status, optimistic transitions, and uninvolved completion
  validation before any reputation or badge effect.

### Remaining

- Cross-domain search does not yet index every new private or restricted
  subject type. Each future index must apply visibility before matching,
  snippets, counts, suggestions, and autocomplete.
- Broad unified-report coverage remains incomplete for community domains that
  have not yet been implemented.

## Data Risks

- Replacing category strings in one deployment would orphan existing topics.
- Reusing a source taxon identifier as the primary key would make source
  changes destructive.
- Loading a complete taxonomy into PHP or browser state would exceed memory
  and Livewire payload budgets.
- In-place synonym merges could destroy historical user selections.
- A single transaction for millions of taxa would be operationally unsafe.

The selected design uses internal keys, source provenance, version activation,
chunk-level transactions, resumable cursors, and expand-and-contract topic
relations.

## Missing Source Detail

The recovered additive extension names the original top-level categories
1–20, but says their earlier exact structured subcategory list came from a
previous prompt. The recovered primary prompt contains the full earlier forum
specification and many category/subcategory concepts, but no separate
numbered 1–20 hierarchy. No source text is fabricated. The implementation
will preserve all recovered atomic category requirements, seed the named
1–20 roots, derive stable children only from recovered source concepts, and
record this provenance in the category manifest.

## Resolved Community Review Gap

Low-risk community review panels and contextual notes are implemented with
eligible balanced assignment, conflict replacement, deadline enforcement,
immutable history, versioned author/moderator changes, revalidation, appeals,
guest-safe projection, and a closed boundary to high-risk moderation.

## Resolved Mentorship Gap

All thirteen required peer-mentorship types now have an additive domain,
policy, Action, factory, demo-seed, translation, Livewire, query-budget, and
privacy test boundary. The implementation deliberately reuses global category,
taxonomy, report, block, reputation, trust, badge, and credential systems.
Private contact remains on-platform and mentorship never projects
professional authority.

## Resolved Persistent Group Core Gap

The relational group core now covers visibility, six bounded roles, reviewed
membership, expiring invitations, member restrictions, ownership transfer,
lifecycle, append-only audit, unified reports, taxon focus, deterministic
definitions, and class-based Livewire management. Private/unlisted
discoverability is scoped before rows and counts.

## Resolved Group Content And Poll Gap

Existing topics and guides now retain identity while gaining authorized group
associations. Durable group activities, announcements, private files, and
single-, multiple-, and ranked-choice polls replace the static group-content
fixtures. Polls enforce typed voter/result/eligibility modes, one locked
user/poll projection, editability, timestamp-derived closure, private voter
identity, and a strict non-authority boundary. Focused authorization,
validation, seed, query-budget, localization, architecture, full-suite, fresh
database, build, and browser evidence is recorded in the package plan and
requirements overlay.

## Resolved Journal And Progress Gap

Forum progress journals now preserve the existing topic shell while adding
typed journal lifecycle, dated entries, queryable measurements, milestones,
setbacks, selected collaborators, parent-scoped comments, immutable edit
history, private content-validated media, protected export, and archival.
Legacy backfill uses only the explicit journal topic type; it never infers
sensitive history from prose or copies private operational care records.
Timezone-normalized validation, optimistic locking, idempotency, direct
Livewire authorization, neutral progress language, and bounded private
projections are verified by focused, full-suite, fresh-database, and
mobile/desktop browser evidence.
