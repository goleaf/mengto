# Integrations

## Current Boundaries

| Capability | Current implementation | Production boundary |
| --- | --- | --- |
| QR codes | Local `bacon/bacon-qr-code` generation | Keep local, validate payload |
| Maps/routes | Server catalog plus OpenStreetMap links and local progressive map | Provider adapter for geocoding/tiles/routing |
| Payments | Explicit prototype order/payment states | Signed webhooks, idempotency, reconciliation |
| Camera/audio/video calls | Browser local preflight only | Audited WebRTC/media provider |
| Device connectivity | Manual/provider-ready device records | Vendor clients/webhooks per manufacturer |
| Notifications | In-app/framework-ready | Mail/push/SMS providers with preferences |
| Image processing | Validation/storage boundary | Central presets through Laravel image API when needed |
| Translation/AI | No provider connected | Explicit consent, privacy, data retention, typed client |

## Client Contract

Every substantial external client defines:

- base URL and credentials through config;
- connect and total timeout;
- safe retry methods/statuses and backoff;
- expected response size and schema;
- rate-limit handling;
- error-to-domain mapping;
- correlation ID and redacted logs;
- idempotency for mutations;
- test fake and stray-request prevention;
- temporary disable/fallback behaviour.

Do not call `env()` in clients. Do not pass raw provider arrays through the
application.

## Webhooks

Verify signature on the raw request, then parse and validate. Persist the
unique provider event ID before applying an idempotent transition. Duplicate,
out-of-order, invalid-signature, malformed, and amount/currency mismatch cases
must be tested.
