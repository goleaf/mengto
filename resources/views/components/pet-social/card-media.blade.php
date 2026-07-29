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
])

<div {{ $attributes->class(['relative']) }}>
    <x-pet-social.responsive-image
        :src="$src"
        :small="$small"
        :medium="$medium"
        :alt="$alt"
        :width="$width"
        :height="$height"
        :sizes="$sizes"
        :eager="$eager"
        @class([
            'w-full object-cover',
            'aspect-[4/3]' => $ratio === 'portrait',
            'aspect-[3/2]' => $ratio !== 'portrait',
        ])
    />

    {{ $slot }}
</div>
