# Portal Navigation

`PrimaryNavigation` is the canonical desktop/mobile route registry and the
shared shell supplies account, messages, notifications, and contextual actions.
Events retain one `meetups.index` entry and all event cards deep-link to
`meetups.show` using stable route binding.

The Point 13 UI reuses page headers, status badges, form fields, error summary,
action groups, notices, empty state, and responsive shell components. It does
not add an event-only sidebar or mobile navigation.

Breadcrumbs, pet switcher, organization switcher, and a command palette are
not canonical global components in the current repository and cannot be
reported as implemented.
