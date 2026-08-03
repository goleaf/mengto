# Portal Route Matrix

The executable source of truth is `routes/web.php`; `php artisan route:list
--json` reported 173 active routes on 2026-08-03. The canonical
`php artisan route:list --except-vendor --json` audit reported 162 first-party
routes and excluded 11 package/runtime endpoints.

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
