# ADR 0002: Blade And Livewire Boundary

Status: accepted

## Context

PawCircle has a large server-rendered Blade interface and small vanilla
JavaScript enhancements. The production baseline requires Livewire 4 but does
not justify rewriting every static component.

## Decision

Keep Blade for server-rendered pages and presentation components. Use normal
class-based Livewire components with separate templates for substantial
server-backed interactive flows, beginning with authentication/account and
selected high-value operations. Do not use Volt.

## Consequences

- Existing route/controller workflows remain reviewable.
- New interactive state gains Livewire validation, authorization, and testing.
- Public component state stays minimal.
- Vanilla map/media integrations require explicit Livewire navigation
  lifecycle handling.
