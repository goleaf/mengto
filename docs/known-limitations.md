# Known Limitations

Only external or environmental blockers belong here. Product work that can be
implemented in this repository is not a limitation.

## Final External And Environmental Limitations

| Limitation | Evidence | Affected requirement | Resolution path |
| --- | --- | --- | --- |
| PHP coverage driver unavailable | PHP 8.5 runtime has neither Xdebug nor PCOV | TEST-COVERAGE-001 | Install/enable a compatible coverage extension, then run Pest coverage |
| Chromium-compatible browser executable unavailable | `npm run test:browser:a11y` stops before navigation with `Chrome was not found`; the isolated application database and server boot succeeded | Quality gate 9 / AUD-13 | Install Chromium or set `CHROME_BIN`, then rerun the loopback browser matrix |
| Physical device providers and hardware are not selected | No production GPS, feeder, fountain/litter, camera, sensor, or smart-door adapter credentials/hardware exist | PRD-DEVICE-003, PRD-DEVICE-004, PRD-DEVICE-005, PRD-DEVICE-006, PRD-DEVICE-007, PRD-DEVICE-008, PRD-DEVICE-013, PRD-DEVICE-014, PRD-DEVICE-015 | Select providers and hardware, complete privacy/security review, configure secrets outside Git, and run provider contract tests |
| Historical Codex prompt entries unavailable in the current local history | `preserve-forum-source-prompt.php --check` cannot find the ten timestamped source entries; the preserved document checksum and the 38,377-record deterministic generator check still pass | Forum source reconstruction gate | Restore the archived history containing the recorded timestamps, then rerun the preservation check without rewriting the immutable source document |

There is no repository-fixable partial work hidden in this document. The
compliance matrix distinguishes verified, externally blocked, and
not-applicable requirements.
