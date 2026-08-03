@props([
    'src',
    'small' => null,
    'medium' => null,
    'alt',
    'width' => 1200,
    'height' => 800,
    'sizes',
    'eager' => false,
    'ratio' => 'landscape',
    'href' => null,
    'linkLabel' => null,
])

<x-linked-media
    :href="$href"
    :label="$linkLabel"
    variant="card"
    data-ui-card-media
    {{ $attributes->class(['relative isolate block min-w-0 overflow-hidden bg-paw-paper']) }}
>
    <x-responsive-image
        :src="$src"
        :small="$small"
        :medium="$medium"
        :alt="$alt"
        :width="$width"
        :height="$height"
        :sizes="$sizes"
        :eager="$eager"
        @class([
            'block w-full object-cover',
            'aspect-[4/3]' => $ratio === 'portrait',
            'aspect-square' => $ratio === 'square',
            'aspect-video' => $ratio === 'wide',
            'aspect-[3/2]' => $ratio === 'landscape',
        ])
    />

    {{ $slot }}
</x-linked-media>
