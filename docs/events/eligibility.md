# Event Eligibility

Pet participation is controlled by `ForumEventPetParticipation`; accessibility
and evidence use explicit status enums where unknown is not confirmed.
Registration validates managed-pet authority, species/taxon scope, active pet
state, age bounds, and event pet mode. Vaccination requirements create manual
review rather than copying medical records or inferring safety.

Each selected pet has its own eligibility row, source, conditions, reviewer,
and timestamps. A generic requirement registry and audited exception aggregate
remain open scope.
