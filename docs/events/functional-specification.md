# Event Functional Specification

The implemented user journey is portal discovery, authorized event creation,
event detail, occurrence selection, participant/pet registration, organizer
review, waitlist handling, check-in, welfare-safe check-out, updates,
invitations, attendee messages, cancellation/rescheduling, review, and report.

State-changing operations reload the model, authorize the concrete resource,
validate state, and write history within the existing Action/service boundary.
Registration and capacity decisions lock the event/occurrence rows. A form
submission is never treated as approval when review or pet verification is
required. Paid registration is unavailable without a provider.

See `state-machines.md`, `registration.md`, and `testing.md` for exact scope.
