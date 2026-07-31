# Content Feed Conflicts And Resolutions

Date: 2026-07-31

## Supplied Generic Stack Versus Repository Stack

The supplied instructions mention Laravel 11/12 and Filament 3. The live
repository uses Laravel 13, PHP 8.5, Blade, and Livewire 4, and does not install
Filament. Repository `AGENTS.md` is authoritative. Content administration will
reuse existing authorized Blade/Livewire and moderation patterns unless a
future requirement explicitly approves a new dependency.

## Native Mobile Language Versus Server-Rendered Web

The specification describes background upload, one-handed native behavior,
widgets, and offline operation. The current product is a responsive web
application. Phase 43 implements browser-supported local drafts, resumable
requests, responsive controls, and honest online/offline states. It does not
claim operating-system background execution or lock-screen widgets.

## Immediate Media Processing Versus Workerless Runtime

The specification expects multi-quality video, captions, scanning, and
derivatives. The repository currently runs without queue workers. The
foundation models the complete state machine and supports safe bounded web
processing; production transcoding and live streaming remain blocked on an
explicit runtime/provider decision rather than being simulated.

## AI Features Versus No Configured Provider

AI writing, alt text, translation, moderation, summaries, duplicate detection,
and ranking are later optional capabilities. No closed draft or media is sent
to an external service until consent, data location, retention, training use,
fallback, cost, and deletion behavior are documented and tested.

## Broad First Release Versus Evidence-Based Delivery

The first-release list spans the complete content platform. It is divided into
phases 35-44 so that schema, privacy, interaction, media, moderation, and
interface behavior can be independently migrated, rolled back, tested, and
evidenced. Unimplemented requirements remain explicitly open; partial UI is
never labelled as a complete content system.
