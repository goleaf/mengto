# Portal Module Registry

Route audit on 2026-08-03 now finds 176 active routes, 165 first-party routes after
excluding framework/Boost endpoints, and no removed routes in this delivery.
The existing authenticated Blade/Livewire shell remains canonical.

| Module | Primary entry | Canonical aggregate or service |
| --- | --- | --- |
| Feed/content | `preview.feed`, `content.index` | content publication/feed services |
| Pets/household access | `pets.index`, `pets.manage.*` | `PetProfile` and manager relationships |
| Health | `medical-records.index` | canonical medical record |
| Care | `care-journals.index` | care journal/task domain |
| Events | `meetups.index` | `ForumEvent`, occurrences, tracks, rooms, sessions |
| Places/devices/lost-and-found | corresponding named directories | existing place, device, search-case domains |
| Marketplace/bookings/orders | `marketplace.index` | listing, booking, order domains |
| Experts/forum/knowledge | corresponding named directories | expert and forum domains |
| Groups/relationships/messages | corresponding named directories | forum groups and social/message services |
| Organizations | `organizations.index`, `organizations.show` | `Organization`, current memberships, invitations, restrictions, audit |
| Discovery/notifications/settings | `discover.index`, notification/profile routes | existing presenter/settings domains |

No parallel user, pet, event, report, notification, or translation system was
introduced by Point 13. The organization tenant aggregate is shared with
organization-only events. A global active-organization switcher, command
palette, and unified calendar aggregate remain open Point 12 scope.
