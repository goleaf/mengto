# Peer Mentorship

Peer mentorship is an optional, private, in-platform workflow for bounded
community support. It does not create professional authority and does not
replace veterinary, medical, legal, emergency, or other qualified help.

Canonical implementation plan:
`docs/plans/forum-phase8-mentorship-work-package.md`.

## Supported Types

The stable `ForumMentorshipType` values are:

- `first-time-owner`
- `new-species-owner`
- `adoption-adaptation`
- `foster-support`
- `training-support`
- `senior-animal-care`
- `special-needs-care`
- `aquarium-setup`
- `terrarium-setup`
- `horse-ownership`
- `farm-animal-care`
- `lost-animal-search`
- `volunteer-onboarding`

Types are independent scopes. Experience in one type, category, or taxon does
not grant authority in another.

## Domain Boundaries

- `ForumMentorProfile` records explicit opt-in, public-safe profile text,
  supported locales, broad location, platform communication, availability,
  capacity, safety acknowledgement, and optimistic-lock version.
- `ForumMentorScope` links one type to optional forum-category and taxon
  context. Its stable key prevents duplicate scopes.
- `ForumMentorship` owns the request and lifecycle. `open_key` prevents a
  duplicate open relationship for one mentor, mentee, and scope.
- `ForumMentorshipMessage`, `ForumMentorshipFeedback`, and
  `ForumMentorshipEvent` are append-only evidence. Database restrictions
  prevent deleting their parent mentorship.
- `MentorMatcher` performs bounded matching. It checks active profile/scope,
  type, locale, platform communication, optional category/taxon/location,
  blocks, capacity, trust, mentoring reputation, and current independent
  credentials.
- `MentorshipEligibility` centralizes activation, capacity, block, and
  professional-verification rules.
- Actions own every mutation and use authorization, validation, transaction,
  locking, idempotency, and audit recording.

The feature reuses the global forum category, animal taxonomy, block, unified
report, reputation, trust, badge, locale, and professional credential domains.
It does not create parallel role, credential, messaging-safety, or reporting
systems.

## Authorization

- The route requires an authenticated, verified, active user.
- An active mentor profile requires verified email plus current `mentor` trust
  or administrator status.
- Only the profile owner updates their profile or scopes.
- A request cannot target the requester, an inactive or blocked user, an
  inactive scope, an incompatible locale/channel, or a full mentor.
- Only the selected mentor responds.
- Only participants view or message a private mentorship.
- Either participant may end, block, report, or submit one completion-feedback
  record.
- Only an administrator who is not a participant validates completion.
- Professional display requires a separate current credential. Karma, trust,
  mentoring reputation, or a badge cannot create professional verification.

Every protected Livewire action repeats the server-side policy or Action
authorization. Locked UI identifiers reduce accidental mutation but are not
treated as authorization.

## Lifecycle

1. A mentor opts in, acknowledges boundaries, and defines one or more scopes.
2. An eligible user receives transparent bounded matches.
3. The user requests mentorship and acknowledges the safety boundary.
4. The mentor accepts with their own acknowledgement or declines.
5. Active participants communicate through the private append-only thread.
6. Either participant may end the relationship, optionally mark the goal
   complete, block the counterpart, or submit a private unified report.
7. Either participant may submit one optional immutable feedback record.
8. An independent administrator may validate a completed mentorship only when
   both acknowledgements exist, each participant posted evidence, neither
   blocks the other, and no related report is open.
9. Validation creates at most one mentoring reputation event and one mentor
   badge grant through idempotent keys.

States are `requested`, `active`, `declined`, `completed`, `ended`, and
`cancelled`. Optimistic `lock_version` checks prevent stale responses and end
operations.

## Reporting And Privacy

The contact-safety form presents all 88 active unified report reasons and
requires the reporter to:

- select a reason;
- provide bounded details;
- explicitly confirm truthfulness;
- optionally mark immediate safety when the reason permits it;
- optionally block the counterpart.

Only participants can report a mentorship. The reported user cannot enumerate
the private mentorship through this path. Reporter identity and private
evidence follow unified moderation policy. Messages, feedback private notes,
exact credentials, payment data, medical records, and exact addresses are not
public.

## Livewire Interface

`/forum/mentorship` composes three class-based components:

- `MentorDiscovery`
- `MentorshipInbox`
- `MentorProfileManager`

Each component has a separate Blade template. Public state is small and
validated as browser-controlled input. Taxon selection reuses the bounded
`AnimalTaxonomySelector`; no taxonomy tree is hydrated into the browser.
Loading, offline, empty, success, validation, and safety states are localized.

The first render has a query-budget assertion of at most 45 queries. Match,
workspace, message, and definition queries are bounded; private results are
not globally cached.

## Seeding

`MentorshipDemoSeeder` is allowed only in environments listed by
`platform.demo_seed_environments`. It creates or synchronizes:

- a current mentor trust assignment;
- one active public mentor profile;
- two independent support scopes;
- one accepted active mentorship;
- one message from each participant.

Stable request and message idempotency keys make repeat execution
non-duplicating. Production-safe definition seeders do not create fake
mentorships. Running the demo seeder directly in an unapproved environment
throws before writing data.

## Operations And Recovery

The migration is additive. Before any mentorship data exists, rollback may
drop its six tables in reverse foreign-key order. After use begins, retain the
tables and recover forward:

1. pause the affected profile or scope;
2. block contact when safety requires it;
3. submit or process the unified report;
4. transition the mentorship through an Action;
5. preserve messages, feedback, and events;
6. reverse any incorrect reputation event through the reputation ledger.

Do not delete or rewrite audit history. If a stale transition occurs, reload
the current `lock_version` and reassess rather than bypassing the check.

## Verification

Focused:

```bash
php artisan test tests/Feature/Forum/MentorshipWorkflowTest.php --stop-on-failure
```

Cross-domain:

```bash
php artisan test tests/Feature/ArchitectureComplianceTest.php tests/Feature/Database/SchemaIntegrityTest.php tests/Feature/Database/FactoryAndSeederTest.php tests/Feature/LocalizationTest.php tests/Feature/Forum/MentorshipWorkflowTest.php --stop-on-failure
```

The focused package currently contains 29 passing tests and 117 assertions.
The integration slice contains 899 passing tests and 43,799 assertions. The
full serial suite contains 1,263 passing tests and 46,373 assertions.

Fresh verification reports 91 migrations and 135 tables; fresh seed and
repeated seed both exit successfully with the user count stable at five.
Larastan reports zero errors and the Vite 8.2.0 production build passes.

Playwright verified 375x812 and 1440x900 with one page heading, no horizontal
overflow, no raw translation keys, no unnamed or unlabeled controls, no
workflow target below 44px, no console warning/error, 88 report-reason
options, an explicit truthfulness checkbox, and a successful real Livewire
message submission.
