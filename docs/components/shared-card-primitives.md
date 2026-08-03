# Shared Card Primitives

The shared card system centralizes stable presentation rules without moving
domain data, permissions, queries, or business states into Blade. Presenters,
controllers, and class-based components must prepare every value before the
view renders.

## Composition Boundary

Use `x-directory-card` only for public directory cards whose durable topology
is media, an opaque body, and an optional bottom footer. Domain-specific
metadata, badges, recommendation reasons, and actions remain in the consuming
component.

Cards without media keep their existing semantic shell or introduce a separate
component after at least two real consumers share the same topology. Do not
pass an empty media slot, add a decorative placeholder solely to fit this
component, or grow `x-directory-card` with domain switches.

```blade
<x-directory-card data-group-card>
    <x-slot:media>
        <x-card-media
            :src="$group['image']"
            :small="$group['image_small']"
            :medium="$group['image_medium']"
            :alt="$group['image_alt']"
            :href="$group['url']"
            :link-label="$group['media_label']"
            sizes="(min-width: 64rem) 33vw, 100vw"
        />
    </x-slot:media>

    <x-card-heading :title="$group['name']" :href="$group['url']" />
    <x-card-description>{{ $group['description'] }}</x-card-description>

    <x-slot:footer>
        {{-- Render already-authorized, localized domain actions here. --}}
    </x-slot:footer>
</x-directory-card>
```

## Primitive Contracts

### `x-directory-card`

- Requires a `media` slot and accepts the default body slot.
- Accepts an optional `footer` slot that stays at the bottom of equal-height
  cards.
- Owns containment, the media/body separator, the opaque body, padding, and
  full-height flex composition.
- Accepts ordinary HTML attributes for stable consumer hooks such as
  `data-group-card`.

### `x-card-media`

- Requires `src`, `alt`, and `sizes`.
- Accepts optional responsive `small` and `medium` sources, intrinsic `width`
  and `height`, `eager`, `href`, and `linkLabel` values.
- Supports `landscape`, `portrait`, `square`, and `wide` ratios. Unknown values
  deliberately normalize to landscape (`3:2`) so arbitrary utility classes
  cannot enter the template.
- Links the image only when an already-authorized `href` is supplied. It never
  resolves routes or permissions.

### `x-card-heading`

- Requires a plain-text `title`; rich HTML and title slots are intentionally
  unsupported.
- Accepts an optional `href`, semantic `level` 2 or 3, and `spacing` of `none`,
  `compact`, `regular`, or `relaxed`.
- Unsupported levels normalize to `h3`; unsupported spacing adds no margin.
- The title remains escaped by Blade.

### `x-card-description`

- Renders escaped slot text with safe wrapping and no truncation.
- Accepts `spacing` of `none`, `compact`, `regular`, or `relaxed`; unsupported
  spacing adds no margin.
- Must not receive trusted-rich-HTML output.

## Leaf-Only Reuse

Cards may reuse heading, description, or media leaves without adopting the
directory shell. This is the correct path when a domain card has a different
topology. `discovery-result-card` reuses the shared heading and description;
`expert-card` reuses the shared heading while retaining qualification, status,
statistics, and actions in its domain component. `place-card` reuses media,
heading, and description leaves while retaining its list/split-map shell and
Places-specific facts and actions.

Do not create a compact-card composition until two real consumers require the
same compact topology. This prevents speculative flags and keeps shared changes
predictable.

## Verification

The server contracts are covered by
`tests/Feature/SharedCardSystemContinuationTest.php` and
`tests/Feature/PlaceSharedCardCompositionTest.php`. The authenticated browser
matrices run with:

```shell
npm run test:browser:groups
npm run test:browser:places
```

The Groups command verifies EN, LT, and RU at 320, 375, 768, 1024, 1440, and 1920 pixels,
including long-copy visibility, media containment, equal same-row heights,
membership states, 44-pixel action targets, translation-key leakage,
horizontal overflow, loaded images, and browser-console errors. The Places
command verifies directory and detail geometry at desktop and mobile widths,
including shared leaves, synchronized map selection, image containment, touch
targets, private-location isolation, and browser-console errors.
