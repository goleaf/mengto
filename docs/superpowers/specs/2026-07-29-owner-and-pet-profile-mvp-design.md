# Owner And Pet Profile MVP Design

## Status

Approved from the user-provided profile specification. This document narrows
the first product area to an interactive Blade prototype that fits the current
session-backed PawCircle architecture.

## Product Boundary

PawCircle has two separate social identities:

- An owner account represents the person using the platform.
- A pet profile represents one animal managed by one or more people.

Following, privacy, sharing, URLs, statistics, and content context are separate
for each identity. An owner may manage multiple pets without merging their
audiences or profile data.

## MVP Scope

This slice implements the profile experience required before the publication
feed is expanded:

- A canonical owner URL and canonical URLs for Scout and Nori.
- Separate owner and pet profile pages with distinct follow targets.
- Multiple pet cards on the owner profile, each opening its own profile.
- Query-backed profile tabs with stable direct links.
- Owner, public, follower, and friend preview modes.
- Separate visibility settings for the owner and for each pet.
- Owner and pet editing through the existing composer workflow.
- Independent share, follow, friend-request, block, and report prototype
  actions.
- Profile completion, verification cues, languages, interests, family/manager
  information, public facts, and privacy explanations.
- Responsive, image-forward layouts built entirely from shared Blade
  components.

Registration, OAuth, email delivery, real uploads, document verification,
moderation queues, medical storage, GPS history, ownership transfer, imports,
QR generation, and permanent deletion require persistence and authenticated
policies. They remain future production layers, not simulated security.

## Routes

Canonical public routes:

- `/@mia-carter` for the owner profile.
- `/@mia-carter/scout` for Scout.
- `/@mia-carter/nori` for Nori.

Existing `/profile/mia-carter` and `/pets/scout` URLs remain compatible through
redirects. Created session-backed pet profiles retain their generated URLs.

The `tab` query selects a profile section. The `view` query selects one of:
`owner`, `public`, `follower`, or `friend`. A Form Request validates both query
values before they reach presentation code.

## Presentation Architecture

`ProfilePresenter` owns owner/pet profile contracts and translates
prototype state into view-ready arrays. It also resolves visibility for the
selected audience. Existing controllers stay invokable and only pass validated
query state to the presenter.

`PrototypeState` stores:

- Owner profile overrides.
- Per-pet profile overrides.
- Owner and pet visibility settings.
- Independent social toggles and submitted reports.

The main owner and pet Blade views each contain one feature component. Feature
components coordinate workflows; object components render typed profile data;
UI components own tabs, controls, progress, media, and feedback.

## Owner Profile

The first viewport contains a responsive cover, avatar, display name, unique
handle, location when visible, account type, verification cues, statistics,
and context-appropriate actions.

Tabs:

- Overview: biography, pets, pinned/recent activity, completion.
- Pets: every visible pet in a full profile card.
- Posts: owner-authored moments.
- About: languages, interests, account details, privacy summary.

Owner mode exposes edit and privacy controls. Other preview modes expose
follow, friend-request, message, share, block, and report actions.

## Pet Profile

Each pet has its own URL, identity, follow target, statistics, owner link, and
social controls. Scout and Nori use the same page contract with different data.

Tabs:

- Feed: pet-specific moments.
- About: story, temperament, species, breed, age, and activity.
- Photos: stable responsive gallery.
- Friends: pet social context and walk invitations.
- Care: shown only when the selected audience may view it.
- Family: owners and managers with explicit human roles.

Owner mode exposes edit and privacy controls. Public modes expose independent
follow, pet-friend, walk, share, block, and report actions.

## Privacy

Visibility levels are:

- Public
- Registered members
- Followers
- Friends
- Owners and managers
- Hidden

Owner and pet settings are stored separately. The presenter applies them to
location, pets, posts, friends, activity, and care information. Exact addresses,
GPS history, private contact data, documents, microchip data, and medical
records are never included in public prototype data.

## Actions

The existing action endpoint gains narrowly scoped operations:

- Toggle an owner or pet follow.
- Toggle an owner or pet friend request.
- Toggle a block.
- Submit a profile report with a reason and description.
- Save owner privacy.
- Save pet privacy.

All inputs pass through the existing Form Request. Actions write only to
prototype session state and return named routes plus flash feedback.

## Accessibility And Responsive Behavior

- Cover media uses stable dimensions and mobile-specific crops.
- Tabs remain horizontally scrollable without page overflow.
- All controls retain at least 44 by 44 pixel touch targets on mobile.
- Profile actions have accessible names and pressed state where appropriate.
- Status is communicated with text and icons, not color alone.
- Hidden profile sections render an explicit privacy notice.
- Heading order, focus visibility, reduced motion, and zoom remain valid.

## Verification

No PHP test files are created or changed. Verification consists of:

- PHP syntax checks and Pint.
- Blade compilation and production asset build.
- Route and component graph audits.
- Playwright flows for owner/pet tabs, audience previews, independent follows,
  editing, privacy changes, report/block actions, and legacy redirects.
- Responsive checks at 320, 375, 768, 1024, and 1440 pixels.
- Overflow, touch target, image, focus, heading, console, network, and reduced
  motion audits.

## Follow-Up

The next product area is the publication feed. It should consume the separate
owner and pet identity contracts introduced here so every post clearly records
whether it was published by a person or on behalf of a specific pet.
