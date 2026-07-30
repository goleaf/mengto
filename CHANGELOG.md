# Changelog

## Unreleased

### Changed

- Added a canonical Laravel engineering standard, architecture, implementation
  plan, compliance matrix, and production deployment checklist.
- Replaced Form Request service-locator calls with container method injection.
- Enabled strict Eloquent behavior outside production and completed projections
  exposed by the stricter checks.
- Consolidated private response headers and added global browser security
  headers.
- Moved Blade computations into prepared data and reusable class components.

### Verified

- Full Laravel feature and architecture suite: 116 tests and 3,881 assertions.
- All 68 migrations applied and all 124 application routes inspected.
- Pint and production Laravel cache compilation.
- Production Vite build.
- Composer strict validation and vulnerability audit.
- NPM production dependency audit.
- Desktop and mobile browser smoke with no console errors or horizontal mobile
  document overflow.
