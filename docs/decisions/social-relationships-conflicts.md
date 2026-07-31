# Social Relationships Conflicts

Date: 2026-07-31

## Repository Stack Versus Supplied Instruction Block

The supplied instruction block describes Laravel 11/12 and Filament 3. The
live repository uses Laravel 13, PHP 8.5, Blade, and class-based Livewire 4 and
does not install Filament. The repository `AGENTS.md` is more specific to this
checkout and prohibits adding Filament without an explicit product benefit.
Therefore this revision uses the installed stack and records Filament as not
applicable.

## Prototype Compatibility Versus Mutual Consent

The existing preview lets one user mark catalogue entries as followed or
accepted in personal encrypted state. Treating those values as canonical
relationships would violate the new bilateral consent and actor-attribution
requirements. The resolution is non-destructive retention plus a reviewable
compatibility report, not automatic promotion.

## Unified Relationship Object Versus Distinct Workflows

The source asks for every social connection to be a separate object while also
requiring specialized request, block, and lifecycle behavior. The resolution
is one typed relationship aggregate, one separate request aggregate, and one
append-only event stream. Messaging, group membership, pet ownership, and
professional service access remain their own domain aggregates.

## Public Count Consistency Versus Hidden Edges

One universal count can leak private relationships. Counts are therefore
viewer-aware and may differ by authorization. Internal operational counts and
public visible counts are distinct projections, even when the interface uses
the same label.
