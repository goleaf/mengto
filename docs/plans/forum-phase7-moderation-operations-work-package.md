# Phase 7 Moderation Operations Work Package

Last reviewed: 2026-07-31.

## Requirement Scope

This package includes:

- report operations: `forum.moderation.0209` through
  `forum.moderation.0218`, `forum.moderation.0222`, and
  `forum.moderation.0223`;
- report/case separation: `forum.moderation.0224` through
  `forum.moderation.0227`;
- action records: `forum.moderation.0260` through
  `forum.moderation.0276`;
- appeals: `forum.moderation.0277` through `forum.moderation.0290`;
- recusal: `forum.moderation.0291` through `forum.moderation.0300`;
- administration: `forum.interface.0034`, `forum.interface.0035`,
  `forum.moderation.0334`, and `forum.moderation.0335`;
- verification: `forum.moderation.0365`, `forum.moderation.0368`,
  `forum.moderation.0369`, `forum.moderation.0370` through
  `forum.moderation.0378`, and `forum.moderation.0380`.

Broader reportable-subject coverage (`forum.moderation.0364`), duplicate
grouping (`forum.moderation.0367`), public transparency
(`forum.moderation.0301` through `forum.moderation.0312`), and report
preservation during forum category synchronization (`forum.moderation.0362`)
remain separate work packages.

## Current Implementation Analysis

The repository already has a unified polymorphic report, 88 seeded reasons,
private attachments, append-only report events, moderation cases, 31 action
definitions, actions, appeals, recusals, factories, policies, and focused
domain tests. `OpenForumModerationCase` is idempotent, critical priority does
not auto-convict, permanent restrictions require a second administrator, and
the original action actor cannot be the sole appeal reviewer.

Before this package, the class-based `AdminDashboard` only listed cases. It
could not open a report, inspect an authorized case, assign/reassign work,
record an action, recuse, inspect submitted appeals, or record an independent
appeal outcome.

## Desired Result

Authorized administrators can operate the full existing moderation path from
one class-based Livewire area. Every browser-supplied identifier is locked or
reloaded and authorized. The interface exposes only the private information
needed for review, writes through domain Actions, records status changes in
append-only report events, supports recusal, and enforces independent appeal
review.

## Affected Files

Implemented additions:

- `AssignForumModerationCase` action;
- `ModerationOperations` class-based Livewire component and separate Blade
  view;
- focused domain and Livewire moderation operations tests.

Implemented modifications:

- moderation actions where case/report status and event history must stay in
  sync;
- `AdminDashboard` state, computed queues, validation, and action methods;
- the separate admin Blade view;
- EN, LT, and RU `forum_admin` translations;
- progress, moderation, testing, changelog, and traceability documents.

## Schema Changes

No new domain table is required. Existing report events, case assignment,
action, appeal, recusal, evidence, retention, and review columns satisfy this
package. The additive FK index migration created during full-suite regression
work supplies leading indexes for all new moderation foreign keys.

## Data Migration, Compatibility, And Rollback

No legacy content is rewritten. Existing reports remain valid with or without
a case. Existing cases, actions, appeals, and recusals remain authoritative.
Rollback removes only the new UI/action code; persisted records remain readable
through the models and existing tests.

## Authorization And Validation

- Only active administrators may list or triage reports and cases.
- Every Livewire method reloads the report/case/action/appeal by server-side ID
  and repeats policy/action authorization.
- Assignment targets must be active administrators.
- Action definitions must be active; rule ID, policy basis, private reason,
  optional end date, target user, and senior approver are validated.
- Senior approval cannot use the action actor.
- Recusal immediately unassigns the moderator and prevents later action.
- An appeal reviewer cannot be the original action actor.

## Translation And Interface

All platform text uses the existing EN/LT/RU catalogues. Tables are
horizontally scrollable with semantic headers. Selection and action controls
have accessible names. Forms provide field errors, precise loading states,
offline status, non-color priority/status labels, and destructive-action
confirmation where relevant.

## Cache, Security, Privacy, And Abuse

Permission-sensitive queues are never cached. Reporter identity is not shown
in the queue. Report details, internal reasons, recusal notes, and appeal
evidence remain administrator-only and hidden from serialization. Actions are
bounded, human-triggered, and never infer guilt from priority. No automatic
permanent action is introduced.

## Tests

Create or update tests for:

- report queue privacy and bounded query output;
- ordinary-member denial for every direct Livewire mutation;
- idempotent case opening;
- assignment and reassignment event history;
- selected case private detail access;
- valid action application and required second reviewer;
- recusal unassignment and subsequent action denial;
- submitted appeal queue and independent review;
- reversed appeal case/report consistency;
- field validation and inactive definition/administrator rejection;
- EN/LT/RU rendering, responsive table containment, and no Blade business
  logic.

## Acceptance Criteria

1. A received report can open exactly one moderation case.
2. An authorized administrator can assign and reassign the case.
3. Every assignment, action, and appeal outcome updates linked report state and
   append-only events.
4. Recusal removes assignment and prevents that moderator from applying an
   action.
5. Action records contain policy basis, rule, actor, target, scope, dates,
   user-visible reason, internal reason, appeal availability, and case.
6. Appeal review excludes the original action actor and corrects state after
   reversal.
7. The interface exposes no reporter identity to unauthorized users.
8. Targeted tests, Pint, Larastan, localization, build, fresh migration/seed,
   and full regression suite pass.

## Verification Procedure

Run:

- focused domain and Livewire moderation tests;
- admin authorization tests;
- architecture and localization checks;
- Pint on modified PHP;
- Larastan on affected classes;
- production Vite build;
- fresh isolated migration and seed;
- full Pest suite;
- desktop/mobile browser review of the moderation tab;
- deterministic requirements generation and evidence checks.

Completion evidence must be written into the evidence overlay and current
progress document. Unimplemented transparency or broad reportable-entity
requirements remain in progress rather than being absorbed into this package.

## Completion Evidence

This bounded work package is implemented and verified. Broader moderation
requirements explicitly excluded by the scope section remain discovered or in
progress.

- `AssignForumModerationCase` synchronizes linked report state and records
  assignment/reassignment events under a case lock.
- Applying an action records all required policy, rule, actor, target, scope,
  date, evidence, reason, appeal, reversal, and case fields, then synchronizes
  linked reports.
- A recusal validates the controlled reason set, unassigns the moderator,
  returns linked reports to review, hides the private note, and prevents later
  action by that moderator.
- Appeal decisions exclude the original actor, prevent a second decision,
  reverse the action when required, and append linked report events.
- The Livewire queue omits reporter identity and private evidence, reloads and
  authorizes browser identifiers, rejects inactive assignees/definitions, and
  provides localized EN/LT/RU states.
- `php artisan test --compact` passed 924 tests with 41,403 assertions.
- `vendor/bin/phpstan analyse --no-progress` passed with zero errors.
- `php artisan migrate:fresh --seed --env=testing --force` completed all 85
  migrations and the complete seeder graph.
- `npm run build` passed.
- Playwright completed triage, assignment, and action at 1440px and verified
  both 375px and 1440px with no root overflow, unnamed visible buttons, raw
  translation keys, console warnings, or console errors.
