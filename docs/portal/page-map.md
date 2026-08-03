# Portal Page Map

The portal uses one shared server-rendered application shell. Primary
directories cover feed, pets, health, care, events, places, lost-and-found,
marketplace, experts, forum, groups, neighbors, and discovery. Supporting
surfaces cover relationships, messages, notifications, profile settings,
management pages, and authorized shared/download views.

Discovery uses the stable page identifier `portal.discovery.index` at
`discover.index`. It is the authenticated recommendation hub, not global
search or a duplicate directory. A validated query and category select a
bounded, explainable projection of current public events, communities, places,
specialists, pet profiles, members, and posts. Cards deep-link to each canonical module;
private/unlisted records, exact locations, blocked identities, and profiles
that disabled recommendations are excluded before presentation. The complete
page contract is `docs/portal/discovery.md`.

Dynamic member detail uses `members.show` with a stable `SocialActor` key. It
is restricted by the authenticated portal boundary and shows only a minimal
public identity, public pet profiles, and publications that pass the current
viewer's canonical content-audience rules.

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

Places retain the existing `places.index` and `places.show` presentation. The
directory and stable dynamic detail slug read policy-scoped persisted records;
private, archived, and foreign unlisted records cannot enter that path. The
existing add-place composer creates an owner-visible unlisted review candidate.
Event creation selects an authorized canonical place and optional venue while
exact access remains a separate audited server action.
