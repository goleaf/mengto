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

The forum category tree is owned by forum taxonomy and uses version 4 keys.
The guest key is `forum:category-tree:v4:locale:{locale}`; member and
administrator variants add `:audience:{audience}`. Guests receive only public
categories, active members receive public/member categories, and active
administrators receive the complete administrative tree. Values contain only
reviewed localized presentation data. The configured taxonomy TTL applies;
category synchronization and administrator changes invalidate all three
audiences for every supported locale. Regeneration uses a 10-second lock with
at most a 2-second wait. A cache or lock failure resolves the bounded source
directly, and a warm read performs zero SQL statements.

The lost-and-found directory statistics are owned by lost-and-found under
`search-cases.directory.stats.v2`. The value is global only because every
aggregate is explicitly limited to publicly visible cases and is locale
neutral. TTL is two minutes. Search-case, sighting, and volunteer saves or
deletes invalidate the key. The marketplace directory uses the equivalent
public, locale-neutral `listings.directory.stats.v2` contract with a five
minute TTL and listing mutation invalidation. Both use 10-second regeneration
locks, wait at most 2 seconds, and fall back to the same source queries if the
cache is unavailable.

The topic-type schema registry remains owned by forum schema under
`forum:topic-type-schemas:v1`. It stores at most 200 shared non-user schema
rows for the configured taxonomy TTL, invalidates on schema writes and
synchronization, uses the same 10-second/2-second stampede boundary, and
falls back to the bounded database/catalogue source on cache failure.

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
