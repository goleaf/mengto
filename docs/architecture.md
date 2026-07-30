# PawCircle Architecture

## System Shape

PawCircle is one Laravel 13 application with server-rendered Blade pages,
class-based Livewire 4 components for intentional interactive flows, Eloquent
persistence, progressive JavaScript enhancements, and a Vite-built Tailwind
and SCSS interface.

Normalized domain records use dedicated Eloquent models. Social modules whose
payloads remain catalog-shaped use encrypted, versioned `UserDomainState`
records behind server-authoritative Actions. Browser sessions may preserve
non-sensitive UI preferences, but are never an authorization, payment,
confidential-storage, social-mutation, or provider-integration boundary.

## Request Flow

Conventional HTTP:

```text
route + middleware
  -> route binding
  -> Form Request normalization / authorization / validation
  -> controller action authorization
  -> Action or cohesive Service
  -> Eloquent transaction / external client
  -> presenter or Resource
  -> Blade / redirect / JSON
```

Livewire:

```text
route or Blade host
  -> class-based component mount authorization
  -> minimal typed public state / form object
  -> action validation + authorization
  -> Action or Service
  -> computed/presentation data
  -> separate Blade template
```

Blade does not perform data access, service resolution, policy decisions, or
business calculations.

## Domain Modules

| Module | Persistence | Primary application boundary |
| --- | --- | --- |
| Identity | `users`, `pet_profiles`, sessions, password reset | Auth controllers/Livewire, policies, `ForumActor` |
| Forum and knowledge | Eloquent | Form Requests, Actions, policies, presenters |
| Experts | Eloquent | Profile/booking Actions and participant policies |
| Marketplace | Eloquent | Locked state transitions and order Actions |
| Lost/found | Eloquent | Search Actions, owner/coordinator policies |
| Medical | Eloquent + private files | Section-scoped grants and download Actions |
| Care | Eloquent + private media | Journal policies, task/entry Actions, grants |
| Devices | Eloquent | Command/read/event/lifecycle Actions, device policies, grants |
| Social | Encrypted/versioned `user_domain_states` plus immutable catalog content | Authenticated Actions, ownership validation, optimistic versioning |
| Places | Immutable catalog plus encrypted/versioned per-user state | Validated filters/actions, provider boundaries |

## Identity Compatibility Boundary

Existing domain tables use string keys such as `owner_key`, `actor_key`,
`buyer_key`, and `seller_key`. A destructive replacement with foreign keys is
not required for production authentication.

`users.actor_key` is the authoritative unique bridge:

- authenticated code derives the current key from `Auth::user()`;
- the browser cannot submit or change the effective actor key;
- existing records remain compatible;
- new records receive the authenticated key;
- a later expand-and-contract migration may add user foreign keys where the
  relationship is unambiguous.

See `docs/decisions/0001-authenticated-actor-keys.md`.

## Data And Transaction Boundaries

- Database constraints protect foreign keys and uniqueness.
- Actions own short transactions and row locks.
- External HTTP does not execute inside a database transaction.
- Payment, device command, medication dose, care entry, sighting, booking,
  webhook, and temporary token operations require idempotency.
- Audit records are written in the same transaction as critical changes where
  rollback consistency matters.
- Side effects execute after commit when their observation before commit would
  be unsafe.

## Presentation Boundaries

- Blade components render prepared data.
- Class-based Livewire components own server-backed interaction.
- Alpine is the Livewire-provided client-state layer; no second Alpine install.
- Existing vanilla JavaScript enhances map, message, and browser-media
  interactions and must initialize/teardown on Livewire navigation.
- Tailwind owns utility tokens and responsive primitives.
- The existing SCSS layer owns mature semantic component selectors until
  measured migrations replace them.

## Runtime Boundaries

Local/default configuration uses SQLite plus database-backed cache, session,
and queue. Tests use in-memory SQLite, array cache/session, and sync queue.

User-visible critical care, medical, safety, and device commands must retain a
safe synchronous or local fallback. Queue-backed operations are allowed only
when deployment provides a worker and the job is idempotent, bounded, and
observable.

## Error Boundary

- Validation failures return localized field errors.
- Authorization failures do not reveal private resource details.
- Expected domain conflicts use explicit exceptions or typed results.
- External dependency failures map to safe recoverable states.
- Unexpected exceptions are reported with a request/incident identifier and
  safe structured context.
- Production never exposes SQL, paths, secrets, or stack traces.

## PHP 8.5 Applicability

| Feature | Applicable | Decision |
| --- | --- | --- |
| URI extension | Yes at URL trust boundaries | Prefer standards-based validation when available; retain framework rule compatibility |
| Pipe operator | No current need | Method chains are clearer in this codebase |
| Clone-with | Candidate | Use only with a justified readonly DTO |
| `#[NoDiscard]` | Candidate | Use for critical internal results only |
| `#[Override]` | Yes | Add to meaningful modified overrides |
| `array_first` / `array_last` | Yes | Use in touched code where clearer |
| Partitioned cookies | No | No cross-site embedded application |
| Persistent cURL sharing | No | Laravel HTTP client remains the boundary |

## Laravel 13 Applicability

| Feature | Use case | Decision / evidence |
| --- | --- | --- |
| Modern `bootstrap/app.php` | Middleware and exception configuration | Retain and extend |
| Origin-aware request forgery protection | All session mutations | Required, never disabled |
| API Resources | Public JSON | Required where JSON contract exists |
| `Cache::touch` | Semantic TTL extension | Not used without a concrete tested case |
| Image API | Media variants | Candidate only when implementing transformations |
| AI/vector/semantic APIs | No current provider requirement | Not applicable |
| Queue attributes/routing | Bounded background work | Apply only with operational worker |

## Architecture Verification

- `tests/Feature/ArchitectureComplianceTest.php`
- policy/authentication feature tests
- Livewire component tests
- Larastan
- route and cache build checks
- browser console and repeated-navigation checks
