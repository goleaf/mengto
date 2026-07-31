# Forum Master Requirements

This catalogue is generated from the immutable source prompt. It normalizes
the implementation contract without replacing or shortening source text.

- Source payload SHA-256: `6f8a7f987c336a2247755cae1c2fd66dea66d83cfbf038b5fe31aa848097d773`
- Atomic requirements: `7284`
- Complete machine-readable catalogue: `docs/requirements/forum-requirements.json`
- Complete traceability index: `docs/traceability/forum-requirements-matrix.md`

- Evidence overlay: `docs/traceability/forum-requirement-evidence.json`

## Domain Counts

| Domain | Atomic requirements |
| --- | ---: |
| animal-taxonomy | 896 |
| forum-category | 1461 |
| forum-feature | 3354 |
| interface | 76 |
| localization | 49 |
| moderation | 469 |
| persistence | 99 |
| planning-and-documentation | 144 |
| reputation-and-trust | 352 |
| search-and-discovery | 147 |
| security-and-privacy | 48 |
| seeding | 37 |
| testing-and-traceability | 152 |

## State Contract

Only the states declared in the JSON catalogue are valid. `blocked` is not
completion. `intentionally-not-applicable` requires evidence and a detailed
technical reason. `verified` requires file-level or test-level evidence.

## Requirement Record Contract

Every record preserves its verbatim source, normalized requirement, source
section and line, impacts, planned/evidence files, test and documentation
identifiers, status, risks, and final result. Status changes are performed by
the traceability updater and must never be inferred merely from file existence.
