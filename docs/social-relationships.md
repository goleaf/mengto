# Social Relationships

## Implemented Foundation

The canonical social graph uses `SocialActor` adapters for existing users,
pet profiles, expert profiles, and forum groups. An adapter is an internal
endpoint, not a second profile or authentication identity. One authoritative
profile has at most one adapter.

`SocialRelationshipRequest` owns bilateral consent and request lifecycle.
`SocialRelationship` owns active directed or symmetric edges.
`SocialRelationshipEvent` is append-only actor-attributed evidence. Settings
control follow policy, enforceable friend-request policy, stored list
visibility preferences, recommendation opt-in, and message-request opt-in.

Implemented edge types are follow, owner friendship, pet friendship, close
circle, mute, restriction, and actor-level block. Family, pet management,
professional service access, medical access, devices, ownership, adoption, and
location remain separate authorization domains.

## Consent And Authorization

- Public follows create one directed relationship; approval-only follows
  create a request.
- Owner and pet friendship are symmetric and require an accepted request.
- Pet actors require current `manage-social` manager authority on every action.
- Friends-of-friends and shared-group policies are derived from durable rows,
  never a client-provided context label.
- Every Livewire action reloads the requested row and authorizes on the server.
- Active keys prevent duplicate open requests and active edges; idempotency
  keys are bound to the same endpoints and operation.

## Privacy And Safety Boundary

The directory selects only bounded public presentation fields and excludes
hidden actors, blocked pairs, exact location, medicine, documents, ownership
evidence, manager structure, and credentials. The current graph, request, and
count views are manager-only. Stored list-visibility settings do not yet imply
a complete public viewer-aware friend/follower projection.

Block currently applies to one social actor pair. Account-wide blocking across
every profile controlled by a user, anti-stalking correlation, recommendations,
messaging requests, minors, safe meetings, temporary location, notifications,
transfer/deletion orchestration, and memorial behavior remain later packages.

## Migration And Backfill

Deploy the additive social migration before social-capable application code.
Then run:

```bash
php artisan social:backfill-actors --dry-run --chunk=500
php artisan social:backfill-actors --chunk=500
php artisan social:backfill-actors --chunk=500
```

The repeated run must retain stable actor and settings counts. Existing
`connections.state.v1` and `pet-friends.state.v1` payloads remain encrypted and
are only reported; they are never promoted into bilateral consent.

Before production social writes, rollback drops only the five additive social
tables. After production writes exist, preserve requests, relationships, and
events and recover with an additive forward fix.

## Interface

`/circle/social` is an authenticated class-based Livewire/Blade relationship
center. It supports actor switching, bounded actor search, follow/friend
actions, inbox/outbox decisions, relationship controls, and settings in EN,
LT, and RU. Existing `/circle/connections` and `/circle/pet-friends` preview
URLs remain available as compatibility views and are not canonical writes.

## Verification

Focused tests are in `tests/Feature/SocialRelationshipFoundationTest.php`.
The release gate also runs migration/seed/backfill repetition, full Pint,
Larastan, the serial test suite, dependency audits, Vite build, cache
compilation, source-preservation checks, requirement generation, and the
desktop/mobile/320px browser accessibility audit.
