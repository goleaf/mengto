# Pet Profile Conflict Register

Date: 2026-07-31

## Framework Baseline

The supplied auxiliary AGENTS block names Laravel 11/12 and Filament 3, while
the repository's authoritative current contract and installed dependencies
use Laravel 13, PHP 8.5, class-based Livewire 4, Blade, and no Filament. The
repository baseline wins. No Filament dependency or duplicate admin panel is
introduced.

## Controller Guidance

The auxiliary instructions prefer thin controllers and Form Requests. The
repository also requires class-based Livewire for interactive workflows.
Read-only canonical routes may use invokable controllers; interactive pet
management uses Livewire form objects, actions, policies, and query services.

## Queue Threshold

The auxiliary block asks for jobs above 200ms, while the master specification
forbids adding unsupported operational dependencies for critical features.
Chunked command/admin execution is used for long backfills. Core profile
creation, authorization, privacy, and lifecycle transitions remain
synchronous and transactional.

## Immutable Source Versus Dated Revisions

The preserved source may not be rewritten, while future requirements must be
appended. The preservation script therefore accepts only the exact legacy
document and appends the exact dated revision. Once appended, any byte change
is rejected by `--check`.
