# ADR 0003: Runtime Execution Model

Status: accepted

## Context

The repository configures database queues, but deployment infrastructure is not
yet proven. Critical care, medical, lost-pet, and device behaviour cannot
silently depend on a missing worker.

## Decision

Synchronous bounded operations remain the default for critical mutations.
Queue only independently retryable side effects when a worker and failed-job
operations are configured. Long operations must otherwise use persisted,
idempotent, resumable web batches.

## Consequences

- Critical user confirmation is not falsely shown before persistence.
- Queue adoption requires deployment and observability evidence.
- Jobs use identifiers/small payloads, after-commit dispatch, retry/backoff,
  timeout, idempotency, and failure tests.
