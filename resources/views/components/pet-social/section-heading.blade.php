@props([
    'eyebrow',
    'title',
    'titleId' => null,
    'size' => 'regular',
    'tone' => 'leaf',
])

<div {{ $attributes->class('pc-section-heading') }}>
    <p @class([
        'pc-section-heading__eyebrow',
        'pc-section-heading__eyebrow--coral' => $tone === 'coral',
    ])>
        {{ $eyebrow }}
    </p>
    <h2
        @if ($titleId) id="{{ $titleId }}" @endif
        @class([
            'pc-section-heading__title',
            'pc-section-heading__title--compact' => $size === 'compact',
            'pc-section-heading__title--directory' => $size === 'directory',
        ])
    >
        {{ $title }}
    </h2>
</div>
