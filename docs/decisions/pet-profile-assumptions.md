# Pet Profile Assumptions

Date: 2026-07-31

1. The new `punkt 2` prompt is an additive revision to the preserved master
   forum/taxonomy specification, not a replacement.
2. The local Codex history entry at timestamp `1785514046` is the authoritative
   byte source because it exactly matches the new user message and is complete.
3. Existing `PetProfile` IDs and `profile_key` values are permanent internal
   identities. A mutable slug is presentation only.
4. Existing `user_id` values represent the initial primary-owner membership
   for backfill, but do not prove external legal ownership.
5. Existing species and breed strings remain user-entered legacy facts until
   they can be matched to the global taxonomy with sufficient confidence.
6. Public-by-default seeded demo rows are fixtures; newly created real pet
   drafts default to private.
7. Current care, medical, device, lost/found, adoption, forum, and event
   modules remain authoritative for their own private records.
8. Optional later-stage integrations listed in source section 200 remain
   mandatory planned requirements, not permission to omit the foundation.
9. No phone-masking, registry, image-analysis, or external AI provider is
   assumed to exist.
