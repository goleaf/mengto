# Event Payments

Event prices are integer minor-unit metadata with ISO currency and refund
policy text. `ForumEventRegistrationService` rejects paid registration because
the repository has no verified event payment provider.

Payment, donation, fee, payout, refund, receipt, webhook, idempotency, and
provider-signature workflows are therefore not implemented. Existing booking
or marketplace state cannot authorize an event ticket payment.
