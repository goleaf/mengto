# Tailwind CSS 4

## Build Contract

- Use stable Tailwind CSS 4.3 or newer compatible 4.x.
- Use `@tailwindcss/vite`.
- Main stylesheet starts with `@import "tailwindcss"`.
- CSS-first source detection includes Blade, PHP class components, JavaScript,
  Livewire templates, and required vendor pagination templates.
- Design tokens use `@theme`.
- Do not build dynamic utility fragments.

The large SCSS component layer is an intentional separate legacy asset. It is
not Tailwind configuration and is not removed until its selectors are
incrementally migrated with browser evidence.

## Feature Applicability Matrix

| Feature | Candidate | Decision | Responsive/accessibility effect | Verification |
| --- | --- | --- | --- | --- |
| `@theme` | Global design tokens | Use | Consistent color/type/spacing/focus | Build and token audit |
| `@source` | Blade, JS, PHP, vendor pagination | Use | Prevent missing production styles | Manifest/CSS smoke |
| `@custom-variant` | Reduced motion/forced colors only if built-ins insufficient | Prefer built-ins | Avoid duplicate semantics | CSS review |
| `@utility` | Repeated one-purpose helper | Use sparingly | Document semantic effect | Source/build check |
| Container queries | Reusable dense dashboard cards | Candidate | Respond to container rather than viewport | Responsive browser check |
| Logical properties | Direction-independent spacing/alignment | Use in new work | Enables RTL-safe layout | Locale/RTL review |
| ARIA/data variants | Status and disclosure controls | Use | Non-JS state styling | Keyboard/screen reader check |
| `has` / `not` | Form and grouped state | Use only when simpler | Keep fallback semantics | Browser support review |
| Dynamic viewport units | Mobile panels | Use where browser chrome matters | Prevent hidden controls | Mobile browser check |
| Reduced-motion variant | Motion/transitions | Required | Removes nonessential movement | Emulation check |
| Forced-colors variant | Critical controls/status | Required where custom color hides meaning | High contrast operability | Forced-colors review |
| Pointer/hover variants | Optional desktop enhancement | Use with non-hover base | Mobile never depends on hover | Touch check |
| Masks/text shadows/zoom | No current product need | Not applicable | Avoid decorative/compat cost | N/A |
| `@apply` | Existing small utility group only | Avoid by default | Components/tokens preferred | Source audit |

## Token Families

Define intentional values for:

- brand, neutral, success, warning, danger, information;
- page/surface backgrounds, borders, focus rings;
- typography, line heights, weights;
- spacing, containers, breakpoints;
- radii no larger than the design contract for ordinary cards;
- shadows, z-index, transitions, durations, and restrained animation.

Use modern color spaces only after contrast verification. Preserve the
PawCircle green/coral/cream/charcoal identity.

## Responsive Contract

Base utilities target 320-pixel mobile. Add behaviour progressively for large
mobile, tablet portrait/landscape, small laptop, desktop, and wide desktop.

Tables use cards/rows or controlled scrolling with retained headers. Tabs and
filter rails may scroll internally without widening the page. Long names,
translation expansion, media, maps, modals, drawers, and pagination must stay
within their containers.

## Verification

- `npm run build`
- no dynamic class construction scan
- CSS source detection test
- 320/375/768/1024/1440/wide browser checks
- reduced-motion and forced-colors review
- computed contrast and focus review for critical controls
