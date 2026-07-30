# PawCircle Groups MVP Design

## Goal

Turn groups into structured communities for breeds, interests, care topics, and local neighborhoods. The first release remains a session-backed Blade prototype, but every interaction must behave like a coherent product flow and be ready to move behind Eloquent models later.

## Scope

The MVP includes:

- a searchable and filterable group directory;
- public and closed groups with distinct membership flows;
- recommendation reasons and reversible dismissal;
- joined, pending, and non-member states;
- per-group notification preferences;
- group detail tabs for overview, posts, discussions, events, members, pets, resources, and rules;
- pinned information, a poll, chat preview, moderators, and safety guidance;
- group reporting through the existing composer pattern;
- an expanded group creation form;
- responsive and keyboard-accessible Blade UI.

Hidden groups, paid memberships, file uploads, regional branches, wiki editing, advanced moderation queues, and real-time chat remain later phases.

## Architecture

### Catalog

`GroupCatalog` owns stable group identity and discovery metadata. It has no session or presentation concerns.

`GroupContentCatalog` owns the structured content shown inside a group: posts, discussions, events, members, pets, resources, rules, chat preview, and poll options.

### State

`GroupState` owns prototype membership, notification, poll, recommendation, and report state under a dedicated session key. One group has one membership state per account.

### Presentation

`GroupPresenter` combines catalog, content, state, created groups, and the current owner. Controllers pass prepared arrays to Blade; views perform no queries or business decisions.

### Actions

The existing action endpoint receives explicit group actions. Every group target is validated against the catalog before state changes.

## Directory UX

The directory keeps the existing page shell and adds:

- search by name, purpose, location, organizer, and tags;
- filters for recommended, joined, local, breed, care, and official groups;
- active, name, and membership sorting;
- privacy and verification labels;
- a clear recommendation reason;
- context-aware join, request, pending, or open actions;
- reversible dismissal for recommendations.

## Detail UX

The group page uses a compact cover and identity area followed by horizontally scrollable tabs. Desktop shows central content with a restrained context sidebar. Mobile collapses to one column and keeps all primary actions at least 44 pixels high.

Closed group visitors can read public identity, rules, and requirements but cannot see member-only content until accepted. The prototype includes a pending state to demonstrate this boundary.

## Safety And Accessibility

- Exact home addresses are never shown.
- Local groups expose only city, district, park, or generalized area.
- Medical content includes an explicit professional-care warning.
- Reports are private and return to the originating group.
- Status is conveyed by icon and text, not color alone.
- Controls are native links, buttons, selects, and radio inputs.
- Images have stable dimensions and alternative text.
- Reduced motion and existing focus styles remain respected.

## Verification

- Pint and PHP syntax checks for touched PHP files.
- Blade cache and route listing.
- Vite production build.
- Runtime scans for `pc-` and `x-pet-social`.
- Playwright flows for public join, closed request and cancellation, notifications, poll voting, report navigation, and responsive layout.
- No new PHP test files.
