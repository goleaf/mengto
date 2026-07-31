# Content Feed Assumptions

Date: 2026-07-31

1. `SocialActor` remains the stable publishing-profile adapter; it does not
   replace the authoritative user, pet, expert, or organization model.
2. No new publication is public by inference. The composer supplies a safe
   context default and the server validates the submitted audience.
3. A block is stronger than friendship, following, group membership, mention,
   repost, bookmark, or a previously issued media URL.
4. Exact location, photo GPS metadata, medical records, documents, private
   messages, and minor identity are excluded from ordinary feed payloads.
5. Existing persistent domain content remains valid throughout additive
   migration; no old URL or record is deleted to create the new foundation.
6. The web application can support local draft recovery progressively, but
   native background execution is outside the current runtime contract.
7. AI assistance is optional and disabled until provider, consent, retention,
   training-use, locale, and sensitive-data rules are explicitly configured.
8. Every requirement remains open until its exact behavior has implementation,
   test, and evidence entries; scenario prose alone is not verification.
