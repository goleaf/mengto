# Security

## Security Model

PawCircle contains public social data and highly sensitive medical, care,
location, camera, household, and device data. Sensitive data is closed by
default and requires authenticated ownership or an explicit scoped grant.

## Implemented And Required Controls

### Identity And Session

- Laravel session authentication.
- Session regeneration after login.
- Session invalidation and CSRF regeneration after logout.
- Rate limits for login, registration, reset, verification, temporary links,
  uploads, integration callbacks, and mutation-heavy endpoints.
- Production prohibition for demo identities and fixed actor fallbacks.

### Authorization

- Policies for model/resource actions.
- Authenticated actor key derived server-side.
- Query scoping before private model retrieval.
- Direct Livewire action authorization.
- Fresh password confirmation for precise device pages and remote commands.
- Separate view, share, export, command, precise location, camera, and
  administrative capabilities.

### Tokens

- Cryptographically random raw token returned once.
- SHA-256 or stronger digest persisted.
- Purpose, owner, scope, expiry, revocation, use, and audit metadata.
- Atomic single use when the grant semantics require it.
- Rate-limited lookup with no raw token logging.

### HTTP And Browser

- CSRF and Laravel 13 origin-aware request forgery protection enabled.
- Security headers for clickjacking, referrer, MIME sniffing, and permissions.
- Escaped output by default.
- No raw user HTML without sanitization.
- Safe redirect and URL validation.

### Files

- Private disks for medical/care/device evidence.
- Generated storage names.
- MIME/content/size/dimension validation.
- Authorization on every download.
- Compensation cleanup on partial failure.

### Integrations

- Configuration-only credentials.
- Explicit connect/total timeouts and response-size limits.
- Safe bounded retries only for idempotent operations.
- Webhook signature and event replay protection.
- Redacted structured logs.
- No real external calls in automated tests.

### Logging

Allowed context includes request ID, user ID, actor key, target type/ID,
provider, external request ID, and attempt number.

Never log passwords, sessions, raw reset/access tokens, authorization headers,
private keys, payment credentials, or complete private records.

## Threat Review

| Threat | Control |
| --- | --- |
| IDOR / cross-owner access | Auth middleware, actor bridge, policies, scoped queries, negative tests |
| Session fixation | Regenerate on login; invalidate on logout |
| CSRF / hostile origin | Laravel CSRF and origin protection |
| XSS | Escaped Blade, no raw HTML, sanitizer boundary |
| SQL injection | Eloquent/query builder, no raw user SQL |
| Mass assignment | Explicit fillable/field maps |
| Token replay | Digest, expiry, revocation, atomic consumption |
| Duplicate payment/device/care command | Idempotency key, unique constraint, lock |
| Path traversal/private file leak | Configured disk, generated paths, authorization |
| SSRF | Scheme/host/IP/redirect/size/time validation |
| Webhook forgery/replay | Signature and provider event uniqueness |
| Cache privacy leak | Actor/role/locale scope and invalidation |
| Covert camera/location access | Explicit time capability, step-up, view audit |
| Log disclosure | Redaction and structured allow-list |

Precise device pages and every remote device command use Laravel's
`password.confirm` middleware. `App\Livewire\Auth\ConfirmPassword` validates the
current password through a rate-limited server action, records the framework's
fresh-confirmation timestamp, and returns only to the intended same-origin
route. Direct command submission without a fresh confirmation has no side
effect.

## Incident Response

1. Preserve safe audit and request identifiers.
2. Revoke sessions, temporary links, device commands, and provider tokens as
   applicable.
3. Disable only the affected integration or remote capability.
4. Keep local safety functions available where possible.
5. Patch and add a regression test.
6. Notify affected users according to legal and product policy.
7. Record the finding and resolution in the changelog and compliance matrix.
