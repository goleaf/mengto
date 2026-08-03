# Portal Design System

The portal uses Blade components, class-based Livewire, CSS/SCSS tokens, and
Tailwind 4 utilities. Flux and Filament are not installed. Module pages share
the same application shell, page/header hierarchy, status semantics, form
fields, feedback, actions, responsive list/table patterns, and navigation.

For events, `ForumEventDirectory` and `ForumEventWorkspace` reuse existing
`page-header`, `status-badge`, `form-field`, `forum-error-summary`, `notice`,
`empty-state`, `action-group`, and shared button/panel classes. Status is
always textual; private access data never enters a generic card.

New component abstractions are added only when at least two real consumers need
the same semantics. Point 13 did not create an isolated event visual theme.
