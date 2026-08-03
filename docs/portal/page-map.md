# Portal Page Map

The portal uses one shared server-rendered application shell. Primary
directories cover feed, pets, health, care, events, places, lost-and-found,
marketplace, experts, forum, groups, neighbors, and discovery. Supporting
surfaces cover relationships, messages, notifications, profile settings,
management pages, and authorized shared/download views.

Events use `ForumEventDirectory` inside `meetups/index.blade.php` and
`ForumEventWorkspace` inside `meetups/show.blade.php`. The directory provides
search/filter/pagination/create; detail provides status, occurrences,
registration, organizer queue, occurrence-scoped responsive schedule,
schedule-manager editor, updates, invitations, messages, review, report,
access details, check-in, and check-out according to policy.

No orphan Point 13 page was added. Existing catalogue/created-content URLs are
compatibility entries into the same shell, not a second event application.

Organizations use `OrganizationDirectory`, `OrganizationWorkspace`, and
`OrganizationInvitationResponse` under the existing authenticated shell. The
directory supports bounded discovery and creation; the workspace exposes only
current membership and role-appropriate controls; the signed invitation page
is account-bound and keeps its raw token out of Livewire state.
