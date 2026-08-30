# Portal Design System

The portal uses Blade components, class-based Livewire, CSS/SCSS tokens, and
Tailwind 4 utilities. Flux and Filament are not installed. Module pages share
the same application shell, status semantics, form fields, feedback, actions,
responsive list/table patterns, and navigation. The global page-identity
contract uses `x-page-header` for ordinary directories and workspaces,
including medical records, care journals, lost-and-found, marketplace,
experts, messages, forum, and events. Its route classifications, retained
resource-led heroes, and evidence ledger live
in `docs/plans/global-page-identity-standardization-plan.md`.

For events, `ForumEventDirectory` and `ForumEventWorkspace` reuse existing
`status-badge`, `form-field`, `forum-error-summary`, `notice`, `empty-state`,
`action-group`, and shared button/panel classes. Their page identity uses the
canonical `x-page-header`; the retired `.forum-header` selector family has no
active Blade or SCSS consumer.
Status is always textual; private access data never enters a generic card.
The event workspace uses one passive `x-event-schedule` agenda for mobile and
desktop; its data is prepared and authorized by the class-based Livewire
component, and its manager controls reuse the established form/button/status
patterns.

New component abstractions are added only when at least two real consumers need
the same semantics. Point 13 did not create an isolated event visual theme.
