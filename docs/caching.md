# Caching

## Policy

Cache only expensive, sufficiently stable, measured data. Never cache
authorization decisions or private model graphs without explicit user/role/
locale scope.

Every cache entry records:

- owner and purpose;
- versioned key format;
- tenant/user/role/locale scope;
- TTL and stale behaviour;
- invalidation triggers;
- lock/stampede strategy;
- unavailable-cache behaviour;
- tests.

## Current Stores

Local default uses the database cache store. Tests use the array store.
Redis is available in the local PHP runtime but is not a production dependency
until deployment confirms it. Memcached is not introduced.

The forum category tree uses `forum:category-tree:v1:locale:{locale}` with the
configured taxonomy TTL. The value contains only shared localized category
presentation data selected from reviewed target/fallback translations or the
immutable server fallback; unreviewed values never enter the rendered cache
payload. Category synchronization and administrator category changes
invalidate every supported locale key. Schema/existence probes run only on a
cache miss, so a warm read executes zero database statements; an empty or
not-yet-migrated database uses the validated immutable manifest fallback.

## Locks

Atomic locks are appropriate for duplicate-sensitive prototype state,
idempotent command creation, cache regeneration, and web-batch ownership. Locks
have bounded timeouts and guaranteed release.

## Prohibitions

- No global cache flush on ordinary mutation.
- No key that omits ownership or locale for dependent data.
- No forever cache without a version and invalidation contract.
- No Eloquent object cache when a stable serialized presentation value is
  sufficient.
- No cache used to mask N+1 or missing indexes.
