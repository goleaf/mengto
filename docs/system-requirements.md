# System Requirements

## Application And Architecture

| ID | Requirement | Roles/entities and boundaries | Success and failure contract |
| --- | --- | --- | --- |
| SYS-APP-001 | The application boots on PHP `>=8.5.0 <8.6.0`, latest stable Laravel 13, and supported SQLite. | All runtime code | Boot, route discovery, config/view cache, migration, and tests succeed without deprecation warnings. |
| SYS-APP-002 | Routes are named, grouped, middleware-protected, and free of business logic. | HTTP entry points | Invalid bindings return 404; unauthorized actions return 403 or auth redirect. |
| SYS-APP-003 | Controllers and Livewire components delegate meaningful operations to Actions or cohesive Services. | Controllers, Livewire, Actions | No duplicated transition logic; transaction and result are independently testable. |
| SYS-APP-004 | Browser, request, external, and device data is treated as untrusted at every boundary. | All actors/entities | Only validated explicit fields reach models; malformed input returns localized errors. |
| SYS-APP-005 | Public JSON uses Resources or explicit typed arrays and never serializes raw models unintentionally. | API clients | Stable shape and status codes; internal exceptions and hidden fields are absent. |

## Authentication And Authorization

| ID | Requirement | Roles/entities and boundaries | Success and failure contract |
| --- | --- | --- | --- |
| SYS-AUTH-001 | Session authentication rotates identifiers on login and invalidates session plus CSRF state on logout. | Visitor/member; session | Authenticated redirect succeeds; fixation and stale session tests fail safely. |
| SYS-AUTH-002 | Policies authorize every protected model action and every Livewire mutation. | Member/admin/temporary grantee | Owner and privilege paths pass; non-owner, wrong scope, blocked, and wrong-state paths deny. |
| SYS-AUTH-003 | Sensitive list queries are ownership/grant scoped before retrieval. | Medical/care/device/order/booking/search | A foreign identifier cannot reveal record existence or fields. |
| SYS-AUTH-004 | High-risk exports, precise GPS, cameras, door controls, ownership transfer, and account security support step-up verification. | Account owner | Fresh confirmation permits; missing/expired confirmation denies without side effect. |
| SYS-AUTH-005 | Every first-party product page, mutation, token share, Livewire upload/preview, and product-media response requires an active authenticated account with a verified email. | Anonymous visitor, member; all portal resources | Anonymous HTML redirects and JSON returns 401 before model binding or persistence; inactive/unverified accounts fail closed; account entry, static assets, and minimal health remain available. |

## Data, Files, And Integrations

| ID | Requirement | Roles/entities and boundaries | Success and failure contract |
| --- | --- | --- | --- |
| SYS-DATA-001 | Models define typed relationships, modern casts, deliberate fillable fields, hidden secrets, and valid factories. | All Eloquent models | Schema/model/factory tests pass; strict Eloquent reports no lazy or missing attributes. |
| SYS-DATA-002 | Multi-record invariants use short transactions, database constraints, locks, and idempotency where concurrency is possible. | Commands, bookings, orders, doses, entries, sightings, tokens | Duplicate/reordered requests produce one result or explicit conflict. |
| SYS-DATA-003 | Migrations preserve existing data and use expand-and-contract for risky changes. | Deployment | Fresh and upgrade paths pass; rollback is defined where meaningful. |
| SYS-FILE-001 | Private media uses configured private disks, generated names, content validation, authorization, and lifecycle cleanup. | Owner/grantee; document/media | Authorized download succeeds; direct/foreign access fails; partial files are removed after failure. |
| SYS-HTTP-001 | Each external provider has a configured client with timeouts, bounded safe retry, response validation, redacted logging, and fakes. | Integration client | Successful response maps to internal DTO; timeout/error maps to explicit domain failure. |
| SYS-WEBHOOK-001 | Webhook signatures and unique provider event IDs are verified before state changes. | Provider event | Replay produces no duplicate mutation; invalid signature denies and audits safely. |

## Livewire And Frontend

| ID | Requirement | Roles/entities and boundaries | Success and failure contract |
| --- | --- | --- | --- |
| SYS-LIVEWIRE-001 | Livewire uses class-based PHP components and separate Blade templates; Volt is prohibited. | Interactive pages | Architecture test passes and components render with authorized minimal state. |
| SYS-LIVEWIRE-002 | Public component state is minimal, typed, serializable, validated, and re-authorized per action. | Livewire actor/entity | Tampered IDs and direct method calls deny; normal flow persists once. |
| SYS-LIVEWIRE-003 | Components expose precise loading, dirty, validation, success, empty, and offline feedback where relevant. | Keyboard/touch/screen-reader users | State changes are perceivable and action-specific without blocking unrelated UI. |
| SYS-FRONTEND-001 | Blade remains a passive escaped presentation layer with no direct data or service access. | Every Blade render | Architecture check rejects forbidden directives/calls. |
| SYS-FRONTEND-002 | Custom JavaScript is progressive enhancement with explicit initialization/teardown across Livewire navigation. | Map, messaging, browser media | Repeated navigation creates no duplicate listeners, timers, tracks, or console errors. |
| SYS-FRONTEND-003 | Full-size publication media uses one progressively enhanced responsive viewer with localized modal semantics, zoom, swipe, keyboard navigation, URL-addressable items, and exact-trigger focus restoration. | Active verified member; portal-visible publication media | With JavaScript disabled the authenticated original image link remains usable; at 320-1920 px the active image and social panel do not overlap or create page overflow. |
| SYS-TAILWIND-001 | Tailwind 4 uses the Vite plugin, CSS-first source detection, and `@theme` tokens. | All interfaces | Production build contains used classes and no unsafe dynamic class construction. |

## Runtime And Operations

| ID | Requirement | Roles/entities and boundaries | Success and failure contract |
| --- | --- | --- | --- |
| SYS-CACHE-001 | Every cache entry defines versioned scoped key, TTL, invalidation, failure behaviour, and tests. | Tenant/user/locale data | Cache hit equals source result; mutation invalidates only affected scope. |
| SYS-RUNTIME-001 | Long-running operations use allowed queue infrastructure or resumable bounded web batches. | Operator/member | Retry/resume is idempotent; progress and terminal error persist. |
| SYS-RUNTIME-002 | Production health checks cover essential dependencies without exposing secrets. | Operator | Healthy returns minimal success; degraded dependency is observable and protected. |
| SYS-LOG-001 | Logs use structured non-sensitive context and bounded reporting. | Support/operator | Incident can be traced by request/user/entity IDs without secret leakage. |

## Cross-Cutting Fields

All system requirements inherit:

- authorization from `docs/authorization.md`;
- validation from explicit Form Requests or Livewire form objects;
- localization from `I18N-*`;
- accessibility from `UI-A11Y-*`;
- performance budgets from `PERF-*`;
- implementation and test paths from the compliance matrix.
