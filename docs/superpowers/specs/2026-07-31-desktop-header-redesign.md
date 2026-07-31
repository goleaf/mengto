# Desktop Header Redesign

## Goal

The PawCircle desktop header must keep brand, discovery, account actions, and
the complete primary navigation readable without compressing search into an
unusable icon-sized field.

## Layout Contract

- Below `80rem`, retain the compact utility header and existing mobile dock.
- At `80rem` and above, use two rows inside the shared `80rem` shell.
- The first row contains brand, a flexible bounded search control, Circle,
  notifications, messages, and an account control with the owner name.
- The second row contains all thirteen primary destinations with one active
  state and no clipped labels.
- Header controls remain at least 44 pixels high.

## Visual System

The redesign retains the existing PawCircle cream, paper, ink, leaf, line,
radius, typography, focus, and shadow tokens. The utility row remains quiet;
the navigation row uses a subtle paper surface and a single ink active state.

## Accessibility And Responsive Rules

- Preserve semantic banner and navigation landmarks.
- Preserve accessible names, tooltips, `aria-current`, and keyboard order.
- Do not add hover-only or JavaScript-dependent navigation.
- At 200 percent zoom, the desktop rows collapse to the compact header and
  mobile dock through the existing breakpoint behavior.
- Do not introduce document-level horizontal overflow.

## Verification

- Feature coverage asserts separate utility and primary rows, all thirteen
  desktop destinations, the unchanged eleven-item mobile dock, and active
  navigation state.
- Browser verification covers `375`, `768`, `1024`, `1280`, `1440`, and
  `1920` pixel widths, visible labels, 44-pixel targets, overflow, keyboard
  focus, and console errors.
