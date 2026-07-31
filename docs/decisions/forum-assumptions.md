# Forum Assumptions

These assumptions were derived from repository evidence and are reviewable.
They do not replace source requirements.

1. `en`, `lt`, and `ru` remain the complete supported locale set.
2. SQLite portability is mandatory for migrations and automated tests.
3. Existing actor keys remain the compatibility identity boundary while new
   relations use database foreign keys where an unambiguous user exists.
4. Current topic, answer, knowledge, lost/found, expert, and marketplace
   records are production-relevant and must be preserved.
5. No Flux or Filament package is installed; the requested administration
   area will use class-based Livewire and existing Blade components.
6. A full multi-million-record taxonomy snapshot is operational input, not
   source code. The repository includes a small core snapshot and importer
   fixtures; production imports use a separately downloaded local archive.
7. Taxonomy synchronization may run by authorized command or resumable web
   batches. Normal application requests never require network access.
8. Existing queues can support optional notifications, but voting,
   moderation, taxonomy browsing, and safety workflows remain correct without
   an always-running worker.
9. “Every model has a factory” applies to ordinary instantiable records.
   Append-only audit/event models may use explicit builders when arbitrary
   factory creation would violate domain invariants; every exemption requires
   evidence in the seeding matrix.
10. Platform-managed catalogue definitions can be seeded idempotently.
    User-created content is never truncated or overwritten by those seeds.
11. Until a general organization entity is implemented, an organization
    adoption provider is represented by the listing owner and that owner's
    expert profile. A separately reviewed organization-purpose credential
    proves only the scoped organization relationship; it does not grant
    unrelated professional authority.
12. Existing `SearchCase` records are the canonical lost-and-found records.
    Modernization adds compatible fields, indexes, actions, and presentation;
    it does not convert or replace them destructively. Existing accessories
    already represent collars and other visible equipment.
