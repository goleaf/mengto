# Security Policy

## Supported Version

Security fixes are applied to the current `main` branch. Historical prototype
commits and unmaintained forks are not supported releases.

## Private Reporting

Do not disclose a suspected vulnerability in a public issue, discussion, pull
request, screenshot, or shared log. Send the repository owner:

- a concise description;
- affected route, component, or integration;
- reproduction steps using non-sensitive test data;
- observed and expected authorization boundary;
- impact and any known workaround.

Do not include real passwords, session cookies, access tokens, private medical
records, precise home/GPS locations, camera media, or production database data.

## Response

The maintainer will acknowledge a usable report, reproduce it in an isolated
environment, classify affected requirements, add a regression test, and release
the smallest complete correction. Reports involving active credential exposure
require immediate credential rotation and session/token revocation.

## Security Baseline

PawCircle requires:

- authenticated and policy-authorized access to private resources;
- server-side validation of every untrusted mutation;
- signed, expiring, purpose-bound, replay-safe temporary access;
- CSRF and origin-aware request-forgery protection;
- rate limits on authentication, tokens, uploads, search, and mutations;
- encrypted transport and protected private storage;
- generated upload names and authorized downloads;
- redacted structured logs;
- dependency audits and production debug disabled.

Implemented controls and test evidence are maintained in
`docs/security.md`, `docs/authorization.md`, and
`docs/requirements/compliance-matrix.md`.

## Safe Verification

Use isolated testing configuration. Never run destructive migrations against a
shared, staging, or production database. Never make real provider requests from
the automated test suite.
