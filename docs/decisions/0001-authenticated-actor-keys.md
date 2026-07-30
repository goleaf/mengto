# ADR 0001: Authenticated Actor Keys

Status: accepted

## Context

Production-oriented modules use string ownership fields such as `owner_key` and
`actor_key`, while the baseline `ForumActor` always returned `mia-carter`.
Replacing every ownership column with a user foreign key in one release would
be a high-risk destructive migration.

## Decision

Add a unique immutable `actor_key` to `users` and derive the effective actor
from the authenticated user. Preserve existing string ownership columns as the
compatibility boundary. Protect all mutations/private routes with
authentication and policies.

## Consequences

- Existing records and URLs remain valid.
- Browser input cannot choose the acting identity.
- Policies can use the actual `User`.
- A later expand-and-contract migration may add user foreign keys after
  complete mapping and verification.
- Guest presentation may use a neutral identity but cannot mutate.

## Rejected Alternatives

- Keep the fixed actor: insecure and not multi-user.
- Rewrite all 60-plus models at once: unnecessary data and rollout risk.
- Trust a session or form actor key: client-controlled authorization defect.
