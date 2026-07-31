# Social Relationships Safety Work Package

Date: 2026-07-31

Status: implemented and release-verified

## Candidate Requirement IDs

These intervals define the candidate package, not a bulk verification claim.
Only independently implemented and tested records may be promoted in the
evidence overlay.

- request preflight and contextual-message safety:
  `social.request.0001-social.request.0013` and
  `social.request.0034-social.request.0048`;
- decline, hidden decline, cooldown, and recipient-controlled repeat rules:
  `social.request.0141-social.request.0199`;
- account-based rate limits, repeated text, new-account controls, low
  acceptance, and block bypass:
  `social.request.0223-social.request.0294`;
- actor versus account block scope and circumvention protection:
  `social.safety.0023-social.safety.0038` and
  `social.safety.0090-social.safety.0122`;
- unwanted-request, stalking, and fraud report intake:
  `social.moderation.0001-social.moderation.0063`;
- high-priority block invalidation:
  `social.data.0120-social.data.0140`;
- request-card block/report controls and accessible confirmation:
  `social.interface.0021-social.interface.0037`,
  `social.interface.0111-social.interface.0124`, and
  `social.interface.0131-social.interface.0138`;
- minimum release safety controls:
  `social.release.0031-social.release.0036` and
  `social.release.0064-social.release.0075`.

## Desired Result

A recipient can decline a request, prevent future requests from the same real
account, block that account and all profiles it currently or later controls,
or submit one structured private report. A sender cannot bypass rate,
duplicate-message, repeat, or account-block controls by switching among pets,
expert profiles, groups, or newly created actors. Profile-only mute, restrict,
and block remain separate controls.

## Additive Schema And Indexes

Create `social_account_blocks` with stable and active keys, blocker and blocked
user identities, initiating represented actors, status, scope, reason,
idempotency, optimistic version, and revoke audit fields. Add account-block
foreign-key support to social events.

Extend relationship requests with a normalized message fingerprint, risk
level/signals, and a permanent recipient `prevent_repeats` decision. Add
indexes for real-account request windows, repeated messages, repeat checks,
and active incoming/outgoing account-block lists. All access uses Eloquent.

## Migration, Backfill, And Rollback

The migration is additive and does not synthesize account blocks from legacy
profile blocks because the old scope is ambiguous. Existing requests receive
safe defaults and remain byte-for-byte meaningful. Rollback removes only the
new indexes/columns/table after application dependencies are rolled back; no
relationship, request, event, profile, or encrypted prototype row is deleted.

## Authorization And Privacy

The authenticated user is always the blocker and reporter. The represented
source actor must be policy-controlled. Account identity for an incoming
request comes from immutable `created_by_user_id`, never a browser-supplied
user ID. Incoming account blocks are not exposed to the blocked person; the
result is a neutral unavailable-contact response. Reports reuse private
reporter/evidence rules and expose only the decision permitted by moderation.

Shared pets are treated conservatively: a blocked user's profiles are hidden,
but an actor also controlled by the blocker is not removed from the blocker's
own management list. Account blocks never revoke pet ownership or manager
permissions.

## Anti-Abuse Rules

Limits are evaluated by real account over indexed rolling windows. New or
unverified accounts receive lower thresholds. Repeated normalized messages,
very low acceptance after a minimum sample, links, contact details, and
control characters cause a neutral validation failure before delivery. A
single failed request is not a public risk label and no automated decision
creates professional or behavioral reputation.

## Interface

The existing class-based Livewire relationship center gains explicit
recipient actions for decline, decline-and-prevent, account block, and report.
The report form names the request/profile, validates a bounded reason and
details, requires truthfulness confirmation, offers account blocking, restores
focus through ordinary Livewire navigation, and uses textual labels plus
icons. Account blocks are listed only to their creator and can be revoked;
revocation never restores ended relationships or removed requests.

## Tests And Acceptance

1. Fresh migration and populated rollback/re-application preserve all old
   social and profile data.
2. One idempotent account block covers user, owned/managed pets, expert, group,
   and profiles created after the block without creating duplicate edges.
3. Account blocks close open requests and active relationships between the two
   account-controlled actor sets, invalidate their graph namespaces, and never
   revoke profile management.
4. Directory, send, accept, and future content-audience checks receive the
   same account-block decision with a neutral response.
5. Hour/day, new-account, duplicate-message, low-acceptance, and permanent
   repeat controls are account based and cannot be bypassed by actor switching.
6. A report is recipient-authorized, idempotent, private, linked to the request
   subject, and optionally removes the request and blocks the real sender.
7. Unrelated users cannot view, report, revoke, or infer another user's block.
8. EN/LT/RU, policy failures, idempotency, query bounds, factory matrix,
   architecture checks, Pint, Larastan, full Pest, migration/seed, build,
   cache, and desktop/mobile/320px browser gates pass before evidence changes.

## Verification Result

- Focused safety: 8 tests, 65 assertions.
- Combined social foundation and safety: 30 tests, 593 assertions.
- Architecture, schema, factory, and accessibility slice: 1,292 tests, 29,221
  assertions.
- Final serial suite: 1,872 tests, 70,764 assertions in 107.953 seconds.
- Isolated fresh database: 103 migrations, 183 tables, repeat seed retained 5
  users before and after the second run.
- Composer validation/audit, npm audit, Pint, Larastan, and Vite build passed.
- Chrome passed the English desktop/mobile/320px and Russian social screens
  with no overflow, unnamed controls, undersized touch targets, raw keys, or
  console errors. The browser gate also exposed and led to a regression fix for
  delegated pet-manager projection of `revoked_at`.
- Percentage coverage was attempted but the PHP 8.5 CLI has neither Xdebug nor
  PCOV; all behavioral, architecture, static-analysis, and browser gates above
  remain complete.
- The evidence overlay promotes exactly 64 independently proven requirement
  IDs. Cross-account/device correlation, minors, messaging, meetings,
  recommendations, notifications, and appeals remain open.
