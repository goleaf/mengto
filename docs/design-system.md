# Portal Design System

The portal uses Blade components, class-based Livewire, CSS/SCSS tokens, and
Tailwind 4 utilities. Flux and Filament are not installed. Module pages share
the same application shell, status semantics, form fields, feedback, actions,
responsive list/table patterns, and navigation. The first page-identity wave
now uses `x-page-header` for reference directories, medical records, care
journals, lost-and-found, marketplace, experts, and messages. The global
migration is not yet complete; its current contract and evidence ledger live
in `docs/plans/global-page-identity-standardization-plan.md`.

For events, `ForumEventDirectory` and `ForumEventWorkspace` reuse existing
`status-badge`, `form-field`, `forum-error-summary`, `notice`, `empty-state`,
`action-group`, and shared button/panel classes. Their page identity still uses
the legacy `forum-header` family and is planned to migrate to `page-header`.
Status is always textual; private access data never enters a generic card.

New component abstractions are added only when at least two real consumers need
the same semantics. Point 13 did not create an isolated event visual theme.
