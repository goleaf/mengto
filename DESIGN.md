---
name: PawCircle
description: A calm neighborhood social workspace for pet owners.
colors:
  neighborhood-canvas: "#f7f4ed"
  quiet-surface: "#fbfaf6"
  primary-ink: "#27312f"
  secondary-ink: "#66706b"
  soft-divider: "#dedbd0"
  action-leaf: "#24745d"
  selected-mint: "#e2f3eb"
  signal-coral: "#b44739"
  warm-highlight: "#f5df91"
  white: "#ffffff"
typography:
  display:
    fontFamily: "Instrument Sans, ui-sans-serif, system-ui, sans-serif"
    fontSize: "2rem"
    fontWeight: 600
    lineHeight: 1.15
    letterSpacing: "0"
  headline:
    fontFamily: "Instrument Sans, ui-sans-serif, system-ui, sans-serif"
    fontSize: "1.5rem"
    fontWeight: 600
    lineHeight: 1.2
    letterSpacing: "0"
  title:
    fontFamily: "Instrument Sans, ui-sans-serif, system-ui, sans-serif"
    fontSize: "1.125rem"
    fontWeight: 600
    lineHeight: 1.35
    letterSpacing: "0"
  body:
    fontFamily: "Instrument Sans, ui-sans-serif, system-ui, sans-serif"
    fontSize: "0.875rem"
    fontWeight: 400
    lineHeight: 1.5
    letterSpacing: "0"
  label:
    fontFamily: "Instrument Sans, ui-sans-serif, system-ui, sans-serif"
    fontSize: "0.75rem"
    fontWeight: 600
    lineHeight: 1.4
    letterSpacing: "0"
rounded:
  control: "6px"
  panel: "8px"
  circle: "9999px"
spacing:
  xs: "4px"
  sm: "8px"
  md: "12px"
  lg: "16px"
  xl: "20px"
  2xl: "24px"
  3xl: "32px"
components:
  button-primary:
    backgroundColor: "{colors.primary-ink}"
    textColor: "{colors.white}"
    rounded: "{rounded.control}"
    padding: "10px 16px"
    height: "40px"
  button-secondary:
    backgroundColor: "{colors.quiet-surface}"
    textColor: "{colors.secondary-ink}"
    rounded: "{rounded.control}"
    padding: "10px 12px"
    height: "40px"
  field:
    backgroundColor: "{colors.white}"
    textColor: "{colors.primary-ink}"
    rounded: "{rounded.control}"
    padding: "10px 12px"
    height: "44px"
  panel:
    backgroundColor: "{colors.white}"
    textColor: "{colors.primary-ink}"
    rounded: "{rounded.panel}"
    padding: "20px"
---

# Design System: PawCircle

## Overview

**Creative North Star: "The Neighborhood Noticeboard"**

PawCircle is a restrained product interface used in short, practical sessions. It should feel like a well-kept local noticeboard: current, welcoming, easy to scan, and built around real pets and people rather than decorative UI.

The system uses compact fixed typography, modest radii, clear borders, and a small semantic palette. Page structure stays quiet so photography, routines, locations, and actions remain the strongest signals.

**Key Characteristics:**
- Mobile-first and useful with one hand.
- Familiar controls with explicit labels and Lucide icons.
- Flat, compact surfaces with restrained depth.
- URL-backed filters and session-backed prototype state.
- Shared Blade primitives as the default construction method.
- Semantic CSS names without product-specific prefixes, such as `.form-actions`, `.connection-card`, and `.page-header`.

## Colors

The palette combines neutral neighborhood surfaces with leaf green for action and coral for limited attention signals.

### Primary
- **Action Leaf** (`#24745d`): focus, active controls, selected navigation, and positive interaction state.
- **Primary Ink** (`#27312f`): body text and primary button surfaces.

### Secondary
- **Signal Coral** (`#b44739`): unread, time-sensitive, or identity accents used sparingly.
- **Selected Mint** (`#e2f3eb`): selected and successful state backgrounds.

### Neutral
- **Neighborhood Canvas** (`#f7f4ed`): application background.
- **Quiet Surface** (`#fbfaf6`): toolbars and secondary surface layers.
- **White** (`#ffffff`): primary content surfaces.
- **Secondary Ink** (`#66706b`): supporting text that still meets contrast requirements.
- **Soft Divider** (`#dedbd0`): borders and structural separators.

**The One Accent Rule.** A control uses one dominant semantic accent at a time; green, coral, and yellow do not compete within one action cluster.

## Typography

**Display Font:** Instrument Sans with a system sans-serif fallback.
**Body Font:** Instrument Sans with a system sans-serif fallback.

**Character:** One humanist sans family keeps the product familiar and legible. Hierarchy comes from size and weight, never negative letter spacing or decorative display treatment.

### Hierarchy
- **Display** (600, `2rem`, `1.15`): profile and detail names.
- **Headline** (600, `1.5rem`, `1.2`): page titles.
- **Title** (600, `1.125rem`, `1.35`): card and section titles.
- **Body** (400, `0.875rem`, `1.5`): descriptions, messages, and profile copy, capped near 70 characters where practical.
- **Label** (600, `0.75rem`, `1.4`): metadata, compact actions, and field support.

**The Fixed Scale Rule.** Product headings use stable rem sizes and wrap naturally; font size does not track viewport width.

## Elevation

PawCircle is flat by default. Depth comes from tonal layers and one-pixel borders. The only resting shadow is the compact control shadow `0 1px 2px rgb(39 49 47 / 8%)`; focus uses a clear two-ring outline.

**The Flat Surface Rule.** Do not combine a panel border with a wide decorative shadow. Use depth only to communicate control, focus, or sticky positioning.

## Components

### Buttons
- **Shape:** compact rectangle with a `6px` radius.
- **Primary:** ink background, white text, minimum `40px` desktop height and `44px` mobile touch target.
- **Hover / Focus:** leaf transition in `160ms`; focus-visible always uses the shared ring.
- **Secondary / Quiet:** paper or transparent surface with a visible border or underline where required.

### Chips
- **Style:** compact bordered controls with plain text and an optional state icon.
- **State:** selected chips use ink background, white text, and `aria-pressed="true"`.

### Cards / Containers
- **Corner Style:** `8px`.
- **Background:** white or quiet surface.
- **Shadow Strategy:** compact shadow only, never ambient decoration.
- **Border:** one-pixel soft divider.
- **Internal Padding:** `16px` to `20px`, reduced only for dense toolbars.

### Inputs / Fields
- **Style:** white surface, one-pixel divider, `6px` radius, minimum `44px` height.
- **Focus:** leaf border plus the shared focus-visible ring.
- **Error / Disabled:** text and icon communicate state; color is supporting information.

### Navigation
- Desktop uses a stable top bar with text destinations and explicit current-page state.
- Mobile uses a safe-area-aware bottom dock with six evenly sized destinations and 44-pixel targets.

### Action Control
- One shared Blade component owns link, submit, toggle, icon, label, active, and disabled contracts.
- Specialized actions may wrap the primitive but must not recreate its interaction states.

### Component Architecture
- Blade components use capability namespaces instead of a product namespace: `<x-ui.*>`, `<x-layout.*>`, `<x-object.*>`, and `<x-feature.*>`.
- `ui` contains portable controls, media, feedback, fields, and small content primitives; it may depend only on other `ui` components.
- `layout` owns page shells, navigation, grids, and structural composition; it may depend only on `ui` and `layout`.
- `object` renders typed visual data such as pets, people, meetups, messages, and profiles; it may depend only on `ui` and `object`.
- `feature` coordinates complete workflows and may compose every lower layer. Pages should be component-only compositions with no direct HTML.
- Component placement follows dependency direction, not visual size. A large read-only profile hero remains an object; a small toolbar that changes a workflow remains a feature.

### Conversations
- Feed reply actions open a dedicated URL-backed thread instead of leaving the user’s context.
- Comment rows use a small identity graphic, explicit pet context, readable timestamp, and an unframed divided-list rhythm.
- Public comments and direct messages share one reply-composer primitive so labels, validation, touch targets, and submit states stay consistent.

### Personal Collections
- My Circle is the visible destination for every saved, followed, joined, and RSVP state.
- Collection filters remain URL-backed and use the shared filter-chip contract.
- One collection renderer owns mixed post, person, pet, group, and meetup cards.
- Empty overview states use real product imagery and direct routes instead of decorative illustration.

### Walk Planner
- Walk plans move through explicit draft, confirmed, completed, and cancelled states without relying on color alone.
- Each repeated plan card composes the shared image, status, action, and filter primitives with small walk-specific meta and timeline components.
- Timing, meeting point, pace, and check-in context stay visible in one scan; related direct messages remain one action away.
- The message-center `Walk plans` filter is derived from active stored plans, not keywords in conversation copy.
- Empty planner states use real pet and neighbor photography with direct routes into the shared composer.

### Sharing and Voice Consent
- Every share action opens one server-rendered Share Hub assembled from the shared context hero, channel row, recipient row, action, image, and detail primitives.
- External channels receive a canonical PawCircle URL; internal sharing creates a normal direct message so the result remains visible and reversible.
- Conversation details keep identity, shared plans, safety context, and contact state together without exposing private contact data.
- Voice remains opt-in: a call request records one neighbor’s intent, stays visibly cancellable, and never implies that a call has started before mutual consent.

### Owner And Pet Profiles
- Owner and pet profiles share one generic profile hero, action list, tab list, badge list, progress meter, and audience-preview contract.
- The owner profile and every pet profile keep separate canonical URLs, follow targets, friend targets, statistics, privacy settings, and editable state.
- Query-backed tabs remain real links so profile sections can be bookmarked, refreshed, and opened without client-side state.
- Audience preview supports owner, public, follower, and friend perspectives; the presenter resolves visibility before Blade renders a section.
- Mobile covers preserve a stable focal area and leave identity, actions, and tabs visible without taking over the first viewport.
- Human manager roles remain explicit on pet profiles. Interface copy never suggests that an animal personally sent a request, post, or invitation.
- Main profile views contain one feature component; all profile variation is assembled from capability-layer components.

### Created Content Lifecycle
- Newly created groups, meetups, and pet profiles open from every directory or profile card through parameter-aware shared link primitives.
- One created-content presenter resolves cards, detail pages, active state, and canonical share targets without adding type logic to Blade pages.
- One feature composition assembles all created detail pages from the existing detail hero, content panel, icon list, definition list, notice, and action primitives.
- Created detail URLs remain server-rendered and return a real 404 when the session-backed item no longer exists.

### Publication Feed
- `BrowseFeedRequest` validates shareable mode, order, format, pet, and page state before the presenter receives it.
- `FeedCatalog` owns immutable sample content and dictionaries; `FeedPresenter` combines it with session state and returns the complete Blade contract.
- `PerformAction` is the only feed mutation boundary. Blade performs no queries and receives stable author, represented-profile, media, interaction, safety, and lifecycle fields.
- The feed page composes stories, toolbar, quick composer, publication cards, and finite pagination. Each card separately composes identity, context, media, social proof, reaction picker, and action menu.
- Photo carousels use horizontal scroll snapping and stable aspect ratios. Video uses native controls, `preload="metadata"`, useful alternative copy, and no autoplay.
- The three-column shell begins at `xl`; narrower desktops retain a readable single-column feed instead of compressing the central stream.
- Session records store normalized post fields, timestamps, lifecycle status, and optional original-post keys. Comments have stable IDs and at most one visual reply level.
- Eloquent models, policies, media jobs, moderation queues, ranking, analytics, and geographic services remain explicit production boundaries.

### Subscriptions And Recommendations
- `ConnectionCatalog` is the immutable allow-list for person, pet, organization, specialist, group, and topic targets.
- `ConnectionPresenter` joins catalog records to session state and returns tabs, counts, filters, actions, settings, and recommendation reasons without exposing mutation logic to Blade.
- Exact namespaced keys such as `owner-ari-jensen` and `pet-mochi` prevent owner and pet audiences from collapsing into one relationship.
- The connection page is a thin composition of one dashboard feature, reusable list/grid features, one connection card, and portable identity, state, and reason objects.
- Following rows remain compact and operational. Recommendation cards may use two columns on wide screens but collapse to one column with 44-pixel actions on mobile.
- Native `details` menus hold per-subscription notifications, favorite, mute, block, and follower-removal commands without hiding the primary follow state.
- Recommendation reasons are visible text. Verification and privacy labels never rely on color alone, and dismissal always offers an undo path.
- The Following feed checks exact subscriptions and excludes muted or blocked targets. Owner and pet publication scopes remain independent.
- Eloquent graph storage, policies, cache invalidation, delivery channels, rate limiting, abuse analysis, and ranking models remain production boundaries.

## Do's and Don'ts

### Do:
- **Do** preserve 44-by-44-pixel touch targets at mobile widths.
- **Do** build pages from shared Blade components and keep queries and business logic outside templates.
- **Do** use Action Leaf (`#24745d`) for focus, active state, and primary interaction.
- **Do** keep one `h1`, logical heading order, visible labels, and meaningful empty states.
- **Do** test at 320, 375, 768, 1024, and 1440 pixels plus 200% text zoom.

### Don't:
- **Don't** use engagement-heavy urgency, infinite-feed cues, or counters as the primary hierarchy.
- **Don't** use marketing heroes, purple gradients, glass effects, decorative blobs, or oversized rounded surfaces.
- **Don't** infantilize the pet-owner experience with novelty controls or decorative animal graphics.
- **Don't** use colored side stripes wider than one pixel on cards or list items.
- **Don't** use tiny controls, low-contrast helper text, color-only states, negative letter spacing, or viewport-scaled type.
