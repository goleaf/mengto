# Known Limitations

Only external or environmental blockers belong here. Product work that can be
implemented in this repository is not a limitation.

## Final External And Environmental Limitations

| Limitation | Evidence | Affected requirement | Accountable owner / decision authority | Resolution path |
| --- | --- | --- | --- | --- |
| PHP coverage driver unavailable | PHP 8.5 runtime has neither Xdebug nor PCOV; `php scripts/run-tests.php --coverage --min=90 --compact` exits before tests | TEST-COVERAGE-001 | Deployment/runtime operator | Install and enable a PHP 8.5-compatible coverage extension, then run the real Pest 90 percent coverage gate |
| Physical device providers and hardware are not selected | No production GPS, feeder, fountain/litter, camera, sensor, or smart-door adapter credentials or hardware evidence exists | PRD-DEVICE-003, PRD-DEVICE-004, PRD-DEVICE-005, PRD-DEVICE-006, PRD-DEVICE-007, PRD-DEVICE-008, PRD-DEVICE-013, PRD-DEVICE-014, PRD-DEVICE-015 | Product owner with security/integration approval | Select providers and hardware, complete privacy/security review, configure secrets outside Git, and run provider contract tests against audited adapters and devices |
| Payment providers are not selected or evidenced | No audited marketplace, expert, or event payment adapter, webhook signature contract, settlement evidence, or provider reconciliation exists | PRD-MARKET-002, PRD-MARKET-003, PRD-EXPERT-003, EVENT-P13-PAYMENTS, DATA-INTEGRITY-004, SEC-INTEGRATION-001 | Product owner with finance, legal, security, and integration approval | Select providers and supported payment flows, document the legal/financial boundary, implement signed idempotent callbacks and reconciliation, and pass sandbox plus failure-path contract tests |
| Realtime calling and external notification delivery providers are absent | No WebRTC peer/signalling provider, push provider, or server-side SMS delivery adapter is configured or evidenced | PRD-SOCIAL-007, SEC-INTEGRATION-001 | Product owner with privacy/security and integration approval | Select providers, document retention and consent, configure secrets outside Git, and verify delivery/session state only from authenticated provider evidence |
| Live map, routing, weather, and AI providers are absent | The repository has no audited geocoder/router, live weather, or external AI adapter and no approved provider credentials or data-processing terms | PRD-PLACE-003, SEC-INTEGRATION-001 | Product owner with privacy, legal, security, and integration approval | Select only required providers, document data minimization and failure behavior, then implement explicit adapters and provider contract tests; retain truthful unavailable/local-only states until then |
| Production mail delivery is not evidenced | A production SMTP/API transport, delivery monitoring, bounded timeout policy, and reset-mail secret-redaction evidence are not available in this checkout | PRD-IDENTITY-001, SEC-AUTH-002, SEC-INTEGRATION-001, OPS-OBSERVABILITY-001 | Deployment/runtime operator with security approval | Configure a real transport outside Git, prohibit log fallback for authentication mail, verify failure signalling and token redaction, and run a controlled delivery smoke test |
| Historical Codex prompt entries unavailable in the current local history | `php scripts/preserve-forum-source-prompt.php --check` cannot find source entry `1785397895`; the preserved document checksum and the 38,377-record deterministic generator check still pass | Forum source reconstruction gate | Repository maintainer with archived-history access | Restore the archived history containing the recorded timestamps, then rerun the preservation check without rewriting the immutable source document |

The external-limitations table contains no repository-fixable partial work.
The compliance matrix distinguishes verified, externally blocked, and
not-applicable requirements.

Chromium is not an environmental blocker in the audited runtime:
`/usr/bin/google-chrome` and `/usr/bin/google-chrome-stable` are available.
The last complete browser attempt against the earlier audit baseline failed
while seeding on an event-registration lazy load. The current working tree has
since passed isolated fresh seed and browser-runner isolation checks, but the
full browser matrix has not yet been rerun on a frozen tree. Any remaining
browser failure is repository-fixable release work and belongs in the final
verification report rather than this external-limitations register.
