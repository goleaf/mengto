@props([
    'eyebrow',
    'title',
    'titleId' => null,
    'size' => 'regular',
    'tone' => 'leaf',
    'level' => 2,
])

<div {{ $attributes->class('section-heading') }}>
    <p @class([
        'section-heading__eyebrow',
        'section-heading__eyebrow--coral' => $tone === 'coral',
    ])>
        {{ $eyebrow }}
    </p>
    @if ((int) $level === 1)
        <h1
            @if ($titleId) id="{{ $titleId }}" @endif
            @class([
                'section-heading__title',
                'section-heading__title--compact' => $size === 'compact',
                'section-heading__title--directory' => $size === 'directory',
                'section-heading__title--feed' => $size === 'feed',
            ])
        >
            {{ $title }}
        </h1>
    @else
        <h2
            @if ($titleId) id="{{ $titleId }}" @endif
            @class([
                'section-heading__title',
                'section-heading__title--compact' => $size === 'compact',
                'section-heading__title--directory' => $size === 'directory',
                'section-heading__title--feed' => $size === 'feed',
            ])
        >
            {{ $title }}
        </h2>
    @endif
</div>
