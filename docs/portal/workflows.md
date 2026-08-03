# Portal Workflow Registry

| Workflow | Current integration |
| --- | --- |
| Registration/onboarding | Reuses authenticated active verified users |
| Pet creation/management | `/pets` lists only owned or actively managed profiles, separates pending invitations from active access, links creation and per-profile management, and sends cross-user discovery to `/discover?category=pets`; event registration selects only managed active profiles |
| Social/group | Group visibility and membership constrain event access |
| Care/medical | Medical data is minimized; no diagnosis copied into events |
| Lost pet | Generic report/emergency data exists; automatic case escalation is open |
| Marketplace/booking/payment | Existing modules remain separate; no fake event payment |
| Shelter/adoption | Adoption day type exists; durable application workflow remains authoritative |
| Notification | Existing `ForumNotification` is reused for supported event notices |
| Organization | Create tenant, invite an account, accept or decline, assign a scoped role, restrict or suspend operations, and use an authorized tenant in the event builder |
| Place and venue | Submit an unlisted canonical place, verify public facts, select an authorized place/venue for an event, grant time-bound exact access, audit reveal, and revoke stale grants after a move |
| Event lifecycle | Create, version, occurrence, schedule tracks/rooms/sessions, register, review, attendance, cancel/archive foundation |
| Discovery | Enter from primary navigation, scan seven bounded recommendation categories with factual reasons, narrow by validated query/category, open the canonical module or member profile, hide an item/category, and reset user-owned preferences |

Deep links always use named routes and stable event keys. Protected exact
locations and participant data are reauthorized at the destination rather than
embedded in link metadata or notification previews.

Discovery deep links follow the same rule. Its source projection applies
domain visibility, archive, account-block, actor-block, and recommendation
settings before a card is built. Exact event/place location and private profile
data never become discovery metadata.
Member deep links repeat actor policy and block checks; post deep links repeat
the canonical publication audience policy.
