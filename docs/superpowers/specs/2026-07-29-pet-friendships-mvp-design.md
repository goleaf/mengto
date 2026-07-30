# Pet Friendships MVP Design

## Product Boundary

Pet friendship is a two-sided relationship between two pet profiles. A person always performs the action on behalf of a pet, and every request identifies both the acting owner and the source pet. Following remains separate and never creates friendship automatically.

This milestone implements the useful first slice of the larger product specification:

- browse established pet friendships;
- review incoming and outgoing requests;
- send, accept, decline, and cancel requests;
- pause, restore, remove, or block a friendship;
- search recommendations by pet name, breed, owner, or broad location;
- filter by friendship intent;
- explain compatibility and points to discuss;
- open the existing walk planner for confirmed friends;
- reach the owner chat without exposing private contact details;
- manage each owned pet independently.

Albums, recurring walks, live location, group circles, anniversaries, verified family links, and moderation workflows remain later milestones.

## Information Architecture

The feature lives at `/circle/pet-friends`. The page has:

1. an owned-pet switcher for Scout and Nori;
2. summary metrics for friends, requests, walk-ready friends, and nearby suggestions;
3. URL-backed tabs for Friends, Requests, Find friends, and Walks;
4. URL-backed query, intent, and sort controls;
5. compact cards that preserve owner attribution and show relationship state;
6. compatibility blocks with shared preferences and points to discuss;
7. immediate feedback and reversible recommendation dismissal.

The pet profile Friends tab remains the public presentation layer. Owners receive a direct Manage pet friends action that opens the center with the correct source pet.

## State Model

One canonical key represents one pair of pets, regardless of request direction. A relationship record contains:

- first and second pet identifiers;
- requester and recipient;
- status: pending, accepted, paused, declined, removed, or blocked;
- one or more relationship intents;
- optional request message and meeting context;
- request and friendship timestamps;
- last shared activity.

The prototype stores these records in a dedicated session service. The catalog remains immutable and allow-listed. All POST actions validate the source pet, target pet, current relationship state, intent, and return URL state before mutation.

## Interface Components

Shared components stay small:

- `pet-friend-dashboard` composes the page;
- `pet-friend-toolbar` owns URL filters;
- `pet-friend-list` handles lists and empty states;
- `pet-friend-card` renders one relationship or recommendation;
- `pet-friend-request-form` collects a short request;
- `compatibility-summary` presents shared signals and cautions;
- existing `connection-identity`, `status-badge`, `tab-list`, `action-control`, `action-form`, and `summary-strip` are reused.

CSS uses semantic unprefixed names such as `.pet-friend-card`, `.compatibility-summary`, and `.pet-friend-toolbar`.

## Safety And Privacy

- Exact home addresses and GPS history never appear.
- Location labels stay broad.
- Compatibility is phrased as guidance, never a safety guarantee.
- Cross-species recommendations include an explicit caution.
- Blocking removes the target from friends, requests, and recommendations.
- Removing a friendship does not erase old public content.
- Request messages are length-limited and plain text.
- Destructive actions live in a secondary menu and provide clear feedback.

## Responsive Behavior

The layout is mobile-first. At 320-768px it uses one card column, horizontally scrollable summary and tab rails, full-width primary actions, and 44px controls. At 1024px and above recommendations use two columns while established relationships remain a scan-friendly list.

## Verification

No new PHP test files are created. Verification uses:

- PHP syntax and Pint for changed PHP files;
- Blade compilation;
- Vite production build;
- route and HTTP smoke checks;
- source and rendered-DOM checks for forbidden `pc-` prefixes;
- Playwright interaction checks for request, acceptance, cancellation, pause, removal, filters, and walk links;
- responsive checks at 320, 375, 768, 1024, and 1440 pixels;
- console, broken-image, duplicate-ID, overflow, and touch-target checks.
