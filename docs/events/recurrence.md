# Event Recurrence

`ForumEventSeries` stores controlled daily, weekly, monthly, custom-interval,
or fixed-list defaults. `ForumEventOccurrence` gives every concrete instance a
stable key and independent status, times, capacity, format, and public/private
location overrides.

Generated recurrence expansion and DST-boundary editing are not yet exposed in
the builder. Seeded weekly occurrences demonstrate instance-level truth rather
than a free-form recurrence string.
