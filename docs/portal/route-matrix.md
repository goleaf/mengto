# Portal Route Matrix

The executable source of truth is `routes/web.php`; `php artisan route:list
--json` is the verification command. On 2026-08-03 it reported 173 active
routes, of which 171 are first-party under the current audit filter.

## Event Routes

| Method | URL | Name | Page type |
| --- | --- | --- | --- |
| GET | `/meetups` | `meetups.index` | authenticated directory/create workspace |
| GET | `/meetups/{event}` | `meetups.show` | policy-bound canonical event detail |
| GET | `/meetups/small-dog-social` | `meetups.small_dog_social` | stable compatibility detail |
| GET | `/meetups/{item}` | `meetups.created` | legacy created-content compatibility |

All four are active. None was removed or redirected in this delivery. Product
access is guarded by active authenticated and verified-account middleware plus
resource policy. Route coverage is mapped in `tests/Support/route-coverage.php`.
