# Portal Workflow Registry

| Workflow | Current event integration |
| --- | --- |
| Registration/onboarding | Reuses authenticated active verified users |
| Pet creation/management | Registration selects only managed active pet profiles |
| Social/group | Group visibility and membership constrain event access |
| Care/medical | Medical data is minimized; no diagnosis copied into events |
| Lost pet | Generic report/emergency data exists; automatic case escalation is open |
| Marketplace/booking/payment | Existing modules remain separate; no fake event payment |
| Shelter/adoption | Adoption day type exists; durable application workflow remains authoritative |
| Notification | Existing `ForumNotification` is reused for supported event notices |
| Event lifecycle | Create, version, occurrence, register, review, attendance, cancel/archive foundation |

Deep links always use named routes and stable event keys. Protected exact
locations and participant data are reauthorized at the destination rather than
embedded in link metadata or notification previews.
