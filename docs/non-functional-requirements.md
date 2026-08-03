# Non-Functional Requirements

## Security And Privacy

| ID | Verifiable requirement |
| --- | --- |
| SEC-AUTH-001 | Login, registration, reset, verification, temporary links, uploads, search, imports, and integration endpoints have purpose-appropriate rate limits. |
| SEC-AUTH-002 | Password, reset, magic, and access tokens are never logged; recoverable tokens are stored only as secure digests and expire. |
| SEC-AUTH-003 | Production demo accounts and fixed prototype identities are impossible; local/demo identities are environment-gated. |
| SEC-AUTH-004 | The exact anonymous allowlist is enforced centrally before route-model binding; product HTML, JSON, mutations, token shares, Livewire uploads/previews, and product media cannot bypass it. |
| SEC-DATA-001 | Medical, care, device, exact location, camera, private contact, and audit data are closed by default. |
| SEC-DATA-002 | Mass assignment cannot alter actor, owner, role, permission, balance, payment, or moderation fields accidentally. |
| SEC-DATA-003 | Caches, sessions, exports, and temporary links cannot leak data across users, roles, scopes, locales, or revoked grants. |
| SEC-WEB-001 | CSRF and Laravel 13 origin-aware request forgery protection remain enabled and tested. |
| SEC-WEB-002 | User content is escaped by default; rich content requires an explicit tested sanitizer. |
| SEC-WEB-003 | Redirects and server-fetched URLs are scheme/host validated and SSRF protections block loopback, private, link-local, metadata, rebinding, oversized, and unsafe redirect targets. |
| SEC-UPLOAD-001 | Uploads validate size, MIME, real content, image dimensions where relevant, and safe generated names; portal-visible photos are re-encoded through the configured framework image driver, served only through authenticated contained responses, and private media retains scoped ownership. |
| SEC-INTEGRATION-001 | External clients redact credentials, constrain time/size/retry, validate responses, and prevent accidental real calls in tests. |
| SEC-AUDIT-001 | Role, access, share, export, payment, command, ownership, and safety-critical transitions retain actor, time, target, result, and safe context. |

## Data Integrity

| ID | Verifiable requirement |
| --- | --- |
| DATA-INTEGRITY-001 | Foreign keys and unique/check constraints protect relationships and business uniqueness where supported. |
| DATA-INTEGRITY-002 | Monetary values are integer minor units or deliberate decimals; floats are prohibited. |
| DATA-INTEGRITY-003 | Time values retain an unambiguous instant and relevant source timezone; travel and daylight changes never silently shift medical schedules. |
| DATA-INTEGRITY-004 | Device, care, payment, webhook, import, and token retries are idempotent. |
| DATA-INTEGRITY-005 | Original device or professional source data remains immutable when a human correction or processed value is added. |
| DATA-INTEGRITY-006 | Important edits preserve version/audit history rather than silently overwriting evidence. |

## Performance

| ID | Verifiable requirement |
| --- | --- |
| PERF-QUERY-001 | Critical list/detail pages have no N+1 queries and use bounded eager loading or aggregate subqueries. |
| PERF-QUERY-002 | No production list loads an unbounded table; pagination strategy matches ordering and scale. |
| PERF-QUERY-003 | Important filtered/sorted query patterns have justified indexes and an explain-plan review when data scale warrants it. |
| PERF-LIVEWIRE-001 | Public Livewire state contains only required scalars/small arrays; expensive independent sections use measured lazy/defer/island boundaries. |
| PERF-ASSET-001 | Production JS/CSS builds are measured; first-party images use dimensions and size variants that avoid layout shift, while public uploads are bounded and optimized so camera originals are not served to cards or full-size viewers. |
| PERF-CACHE-001 | Cache improves a measured stable operation and prevents stampede; cache is not used to conceal a bad query. |

## Accessibility And Responsive Design

| ID | Verifiable requirement |
| --- | --- |
| UI-A11Y-001 | Critical workflows are keyboard complete with visible focus, logical order, modal focus trap/restoration, and non-drag alternatives. |
| UI-A11Y-002 | Pages use semantic landmarks, one logical `h1`, nested headings, labels, field-linked errors, table headers, and meaningful links. |
| UI-A11Y-003 | Status is conveyed with text/icon in addition to color and announced where asynchronous. |
| UI-A11Y-004 | Images have useful alt text or empty decorative alt; maps/charts have table or textual equivalents. |
| UI-A11Y-005 | Motion respects reduced-motion, critical controls remain usable in forced colors, and text/background contrast meets WCAG 2.1 AA. |
| UI-RESPONSIVE-001 | No horizontal page overflow occurs at 320, 375, 768, 1024, 1440, or wide desktop widths. |
| UI-RESPONSIVE-002 | Controls have adequate touch targets, translated text can expand, and no mobile workflow depends on hover. |
| UI-STATE-001 | Data-driven surfaces define initial/action loading, empty, filtered empty, success, recoverable/fatal error, offline, unauthorized, disabled, pending, and completed states as applicable. |

## Localization

| ID | Verifiable requirement |
| --- | --- |
| I18N-001 | `en`, `lt`, and `ru` remain supported with `en` fallback and one Laravel localization architecture. |
| I18N-002 | First-party user-facing text in PHP, Blade, JavaScript, validation, notifications, mail, and user API errors uses stable translation keys. |
| I18N-003 | Placeholder sets, plural forms, and nested keys are consistent across locales and missing keys do not appear in critical pages. |
| I18N-004 | Dates, times, relative time, numbers, percentages, currency, lists, and measurements use locale-aware formatting and the user's timezone. |
| I18N-005 | Locale selection is validated, persisted, share-safe, and never exposes private state in URLs. |

## Testing And Quality

| ID | Verifiable requirement |
| --- | --- |
| TEST-FEATURE-001 | Every first-party route and protected entry point has success, authentication, authorization, validation, and not-found coverage as applicable. |
| TEST-POLICY-001 | Every policy covers owner, non-owner, privileged role, wrong scope/tenant, blocked/inactive actor, and invalid resource state as applicable. |
| TEST-LIVEWIRE-001 | Every Livewire component covers initial render, tampered state, direct action authorization, validation, repeated submission, loading/offline markup, and redirect/result. |
| TEST-DATA-001 | Fresh migrations, important constraints, casts, relationships, transactions, idempotency, factories, states, and seeders are covered. |
| TEST-SECURITY-001 | Regression tests cover every fixed authorization, token, session, upload, XSS, SSRF, webhook, path, logging, and serialization issue. |
| TEST-ARCH-001 | Automated checks reject Volt, `@php`, `@endphp`, forbidden Blade calls, `env()` outside config, debug code, unsafe dynamic Tailwind classes, and forbidden dependencies. |
| TEST-COVERAGE-001 | Critical auth, authorization, ownership, token, payment, and state-transition branches have complete meaningful coverage; total application-code target is at least 90% where a coverage driver exists. |
| TEST-QUALITY-001 | Pint, Larastan at the documented level, Composer validation/audit, NPM audit, Vite build, and applicable browser checks pass before completion. |

## Factories, Seeding, And Operations

| ID | Verifiable requirement |
| --- | --- |
| SEED-MODEL-001 | Every first-party Eloquent model has a factory or documented precise exemption. |
| SEED-STATE-001 | Factories provide valid defaults and meaningful enum/workflow/privacy/role/edge states without surprising large graphs. |
| SEED-REFERENCE-001 | Fixed reference seeders are deterministic and idempotent. |
| SEED-DEMO-001 | Demo seeding creates realistic empty, normal, role, ownership, privacy, failure, localized, and high-volume workflows only outside production. |
| SEED-SAFETY-001 | `DatabaseSeeder` is repeatable, never truncates production data, and cannot create demo credentials in production. |
| OPS-DEPLOYMENT-001 | Deployment validates PHP/extensions, installs locked dependencies, builds assets, backs up risky data, migrates safely, warms allowed caches, and performs health smokes. |
| OPS-ROLLBACK-001 | Risky dependency/schema releases have a documented rollback or forward-fix path and do not require destructive database rebuilds. |
| OPS-OBSERVABILITY-001 | Logs, health, failed integrations/jobs, storage growth, and security events have owners and retention policies. |
| OPS-DOCS-001 | First-party documentation, requirement matrix, plan, and changelog match the same released behaviour. |

## Traceability

The compliance matrix records implementation files, schema, policies,
validation, frontend, translations, factories, tests, commands, status, and
blockers for each requirement.
