# Seven Web Attack Surfaces Hardening

Date: 2026-07-31

## Goal

Apply the seven Laravel attack surfaces from the referenced security review as
an enforceable PawCircle contract rather than a one-time checklist.

## Controls

| Attack surface | PawCircle control and evidence |
| --- | --- |
| Search and SQL injection | Eloquent binding is mandatory; `ArchitectureComplianceTest` rejects raw SQL and raw query-builder methods. |
| File uploads | Form Requests validate content-derived MIME and size, private evidence uses generated local-disk paths, and a real PHP-content upload regression proves rejection. Architecture checks reject public `move()` calls and client filenames passed to `storeAs()`. |
| Comments and XSS | Blade output stays escaped; the passive-Blade architecture check rejects raw `{!! !!}` rendering and PHP execution in templates. |
| URLs and IDOR | Policies authorize private parents, child-parent consistency is checked server-side, and mismatched medical document identifiers now return privacy-safe `404` without download or audit side effects. |
| Login brute force | `AuthenticateUser` limits normalized email and IP pairs to five failed attempts per minute; a direct Livewire regression proves the sixth attempt remains blocked even with the correct password. |
| API mass assignment | Models use explicit fillable allow-lists, Actions hand-craft persisted fields, registration ignores injected identity/privilege fields, and architecture checks reject unfiltered `Request::all()` payloads. |
| Cookies and sessions | Production forces `Secure`, `HttpOnly`, `SameSite=Lax`, and JSON serialization even if weaker cookie environment values are supplied. Login regenerates and logout invalidates the session. |

## Compatibility

Local and test environments may keep non-secure cookies for HTTP development.
Production cookie hardening is intentionally fail-closed. Existing allowed
upload formats, public media behavior, authorization rules, and URLs do not
change.

## Verification

`tests/Feature/SecurityAttackSurfaceTest.php` covers session configuration,
login throttling, overposting, executable-content uploads, and nested private
document identifiers. `tests/Feature/ArchitectureComplianceTest.php` keeps the
SQL, Blade, model, request-payload, and upload-path restrictions durable.
