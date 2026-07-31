# Social Relationships Assumptions

Date: 2026-07-31

1. The authenticated `User` remains the legal and security actor even when an
   action is presented from a pet, expert, or organization profile.
2. A pet manager needs an explicit social-management permission before acting
   from that pet; ownership is not inferred from a friendship.
3. A public profile may allow immediate follows. A private profile requires a
   follow request.
4. Friendship is symmetric; follow, close circle, mute, restriction, and block
   are directed.
5. “Unknown” and absent social compatibility data are neutral, not negative.
6. Exact address, precise location, medical history, private group membership,
   family relationships, and ownership evidence are outside social
   relationship payloads.
7. The first package runs without queues, websocket infrastructure, external
   search, phone masking, or automated identity providers.
8. Expiry is enforced from timestamps at request time; a later scheduled job
   may materialize expired state for operational reporting.
