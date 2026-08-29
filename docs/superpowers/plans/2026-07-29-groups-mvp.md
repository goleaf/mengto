# Implementation Plan: PawCircle Groups MVP

> Historical delivery record. Production requirements, security rules, testing gates, and runtime versions are governed by `docs/index.md` and its canonical documents. Unchecked boxes below are preserved prototype history, not current PawCircle backlog items.

## Architecture Decisions

- Keep the feature session-backed because the current application is an interactive prototype.
- Separate stable catalog data, structured content, state transitions, and view presentation.
- Preserve the existing action endpoint and Blade design system.
- Keep existing created-group routes working while adding detail routes for every built-in group.
- Use URL state for directory filters and group tabs.

## Phase 1: Foundation

- [ ] Add a strict browse request for directory filters and group tabs.
- [ ] Build the stable group catalog.
- [ ] Build the structured group content catalog.
- [ ] Build membership, notification, poll, dismissal, and report session state.

### Checkpoint

- [ ] New classes pass PHP syntax checks.
- [ ] No existing routes are removed.

## Phase 2: Directory Slice

- [ ] Build `GroupPresenter::directory()`.
- [ ] Switch the group directory controller to the presenter.
- [ ] Upgrade group cards with privacy, verification, recommendation reasons, and contextual actions.
- [ ] Add joined, local, breed, care, and official filters.
- [ ] Add reversible recommendation dismissal.

### Checkpoint

- [ ] Search and filters preserve query state.
- [ ] Public and closed cards show different actions.
- [ ] Empty states remain usable.

## Phase 3: Membership Slice

- [ ] Add join, request, cancel, leave, and notification actions.
- [ ] Validate every group target and transition.
- [ ] Return users to the correct group or directory state.
- [ ] Show clear flash feedback after every action.

### Checkpoint

- [ ] Public group join is immediate.
- [ ] Closed group request becomes pending.
- [ ] Pending request can be cancelled.
- [ ] Joined membership can be left.

## Phase 4: Detail Slice

- [ ] Add a generic detail route for built-in groups.
- [ ] Build a group hero and tab navigation.
- [ ] Build overview, posts, discussions, events, members, pets, resources, rules, and chat views from shared components.
- [ ] Hide member-only sections for closed-group visitors.
- [ ] Add notification settings and membership controls.

### Checkpoint

- [ ] Every catalog group has a working detail page.
- [ ] Tab URLs are shareable.
- [ ] Mobile and desktop layouts remain stable.

## Phase 5: Interaction Slice

- [ ] Add a working group poll.
- [ ] Add group report composer and action.
- [ ] Expand the create-group form with privacy, city, language, rules, pet identity, and notification defaults.
- [ ] Preserve created-group directory and detail behavior.

### Checkpoint

- [ ] Poll selection persists in the session.
- [ ] Reports are stored privately and return to the group.
- [ ] Newly created groups still appear in the directory.

## Phase 6: Styling And Verification

- [ ] Add focused group SCSS using semantic class names.
- [ ] Verify 390, 768, 1280, and 1440 pixel layouts.
- [ ] Verify keyboard labels, control dimensions, image loading, duplicate IDs, and console output.
- [ ] Run Pint, Blade cache, route list, Vite build, prefix scans, and diff checks.
- [ ] Remove generated browser artifacts.

## Risks

- Existing group code is mixed into a large preview service. Mitigation: migrate only group routes to the new presenter and leave unrelated consumers untouched.
- The shared worktree contains broad in-progress changes. Mitigation: edit only named group files and additive shared contracts.
- Session state is not production persistence. Mitigation: keep state transitions behind one service so Eloquent replacement remains localized.
